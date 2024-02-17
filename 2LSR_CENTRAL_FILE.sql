----#### DATABASE STRUCTURE ####----

-- deletes a database with the same name to avoid errors
DROP DATABASE IF EXISTS 2LSR_TEST_DB;
-- creates the database
CREATE DATABASE 2LSR_TEST_DB;
-- specifies the database with that
-- the following functions or commands are going to be utilised
USE 2LSR_TEST_DB;

-- creates a tables to contain data about products, drivers, customers orders and their list of products
-- along with tables where each of the deleted data will be
CREATE TABLE IF NOT EXISTS PRODUCTS
(
    ProdID INT NOT NULL auto_increment,
    ProdName VARCHAR(30),
    ProdPrice DECIMAL(18,2),
    ProdRemain DECIMAL (18,2),
    PRIMARY KEY (ProdID)
);
CREATE TABLE IF NOT EXISTS DELETED_PRODUCTS LIKE PRODUCTS;


CREATE TABLE IF NOT EXISTS DRIVERS
(
    DriverID INT NOT NULL auto_increment,
    DriverName VARCHAR(80),
    DriverPhone VARCHAR(15),
    PRIMARY KEY (DriverID)
);
CREATE TABLE IF NOT EXISTS DELETED_DRIVERS LIKE DRIVERS;

CREATE TABLE IF NOT EXISTS CUSTOMERS
(
    CusID INT NOT NULL auto_increment,
    CusName VARCHAR(80),
    CusAddressLatitude DECIMAL(12,9),
    CusAddressLongtitude DECIMAL(12,9),
    CusPhone VARCHAR(15),
    CusLineID VARCHAR(20),
    NoteToDriver TEXT,
    PRIMARY KEY (CusID)
);
CREATE TABLE IF NOT EXISTS DELETED_CUSTOMERS LIKE CUSTOMERS;

CREATE TABLE IF NOT EXISTS ORDERS
(
    OrdID INT NOT NULL AUTO_INCREMENT,
    CusID INT NOT NULL,
    DriverID INT,
    OrdTotal DECIMAL(18, 2),
    TimeOfOrdering DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    ExpectedDeliveryTime DATETIME,
    OrdStatus VARCHAR(15) NOT NULL DEFAULT 'ONGOING',
    PRIMARY KEY (OrdID)
);
CREATE TABLE IF NOT EXISTS DELETED_ORDERS LIKE ORDERS;

CREATE TABLE IF NOT EXISTS ORDER_PRODUCT_LISTS
(
    OrdID INT NOT NULL,
    ProdID INT NOT NULL,
    ProdQuan DECIMAL(18,2)
);
CREATE TABLE IF NOT EXISTS DELETED_PRODLIST LIKE ORDER_PRODUCT_LISTS;


----#### TRIGGERS ####----

--
-- create a trigger that calculates each order's total after insertion
--
DELIMITER $
CREATE TRIGGER CalculateOrderTotal AFTER INSERT ON ORDER_PRODUCT_LISTS
    FOR EACH ROW
    BEGIN
        -- calculate the total amount,
        -- to which the earliest inserted data belongs,
        -- into a temporary variable "OrderTotal"
        SELECT SUM(p.ProdPrice*opl.ProdQuan)
            INTO @OrderTotal
            FROM PRODUCTS p, ORDER_PRODUCT_LISTS opl
            WHERE opl.ProdID = p.ProdID
            AND opl.OrdID = NEW.OrdID
            GROUP BY NEW.OrdID;
        
        -- update the column "OrdTotal" in the orders table
        -- using the OrderTotal variable made earlier
        UPDATE ORDERS
            SET ORDERS.OrdTotal = @OrderTotal
            WHERE ORDERS.OrdID = NEW.OrdID;
    END$
DELIMITER ;

--
-- create a trigger that updates each order's total after an amount update of a product
--
DELIMITER $
CREATE TRIGGER UpdateOnOrderTotal AFTER UPDATE ON ORDER_PRODUCT_LISTS
    FOR EACH ROW
    BEGIN
        -- calculate the difference in total after the update of certain products
        -- into a temporary variable "diff"
        SELECT (p.ProdPrice*NEW.ProdQuan) - (p.ProdPrice*OLD.ProdQuan)
            INTO @diff
            FROM PRODUCTS p
            WHERE p.ProdID = NEW.ProdID;
        
        -- update the column "OrdTotal" in the orders table
        -- by adding the diff variable made earlier to it
        UPDATE ORDERS
            SET ORDERS.OrdTotal = ORDERS.OrdTotal + @diff
            WHERE ORDERS.OrdID = NEW.OrdID;
    END$
DELIMITER ;

--
-- create a trigger that updates each order's total after a deletion
--
DELIMITER $
CREATE TRIGGER DeleteOnOrderTotal BEFORE DELETE ON ORDER_PRODUCT_LISTS
    FOR EACH ROW
    BEGIN
        -- count the corresponding order in the ORDERS table into the tmeporary "OrderExistenceCheck" variable
        -- to see if the order exists or not
        SELECT COUNT(ORDERS.OrdID) INTO @OrderExistenceCheck FROM ORDERS WHERE ORDERS.OrdID = OLD.OrdID;
        IF @OrderExistenceCheck = 1 THEN
            -- calculate the total amount of the removed product
            -- into a temporary varianble "RemovedTotal".
            SELECT p.ProdPrice*OLD.ProdQuan
                INTO @RemovedTotal
                FROM PRODUCTS p
                WHERE p.ProdID = OLD.ProdID;
            -- update the column "OrdTotal" in the orders table
            -- by subtracting it with the DeletedTotal variable made earlier
            UPDATE ORDERS
                SET ORDERS.OrdTotal = ORDERS.OrdTotal - @RemovedTotal
                WHERE ORDERS.OrdID = OLD.OrdID;
        END IF;
    END$
DELIMITER ;


----#### STORED PROCEDURE ####----

/*
create a procedure that deletes and makes backup data in a specified table
before the data was deleted from its original table
by duplicating the whole row into an alternate table made to store deleted data
using each row's primary key(ID)
*/
DELIMITER $
CREATE PROCEDURE DeleteData(DesTable VARCHAR(30), DesID INT, SecID INT)
-- DesTable means Designated Table
-- DesID means Designated ID for the Designated Row
-- SecID means Designated "Product" ID in case the the user needs to delete a certain item from the ORDER_PRODUCT_LISTS
    BEGIN
        -- use the parameter "DesTable" to point out on which table to delete data
        CASE DesTable
        WHEN 'PRODUCTS' THEN
            INSERT INTO DELETED_PRODUCTS
                SELECT * FROM PRODUCTS
                WHERE PRODUCTS.ProdID = DesID;
            DELETE FROM PRODUCTS 
                WHERE PRODUCTS.ProdID = DesID;

        WHEN 'DRIVERS' THEN
            INSERT INTO DELETED_DRIVERS
                SELECT * FROM DRIVERS
                WHERE DRIVERS.DriverID = DesID;
            DELETE FROM DRIVERS 
                WHERE DRIVERS.DriverID = DesID;

        WHEN 'CUSTOMERS' THEN
            INSERT INTO DELETED_CUSTOMERS
                SELECT * FROM CUSTOMERS
                WHERE CUSTOMERS.CusID = DesID;
            DELETE FROM CUSTOMERS 
                WHERE CUSTOMERS.CusID = DesID;

        WHEN 'ORDERS' THEN
            INSERT INTO DELETED_ORDERS
                SELECT * FROM ORDERS
                WHERE ORDERS.OrdID = DesID;
            DELETE FROM ORDERS 
                WHERE ORDERS.OrdID = DesID;
            DELETE FROM ORDER_PRODUCT_LISTS
                WHERE ORDER_PRODUCT_LISTS.OrdID = DesID;
            -- Aside from the description earlier,
            -- the deletion of an order will lead to a deletion of its list of products.
            -- In this case, any data involving the deleted order will be automatically deleted.
        WHEN 'ORDER_PRODUCT_LISTS' THEN
            -- check to see if the varialble "SecID"
            IF SecID IS NOT NULL THEN
                INSERT INTO DELETED_PRODLIST
                    SELECT * FROM ORDER_PRODUCT_LISTS
                    WHERE ORDER_PRODUCT_LISTS.OrdID = DesID
                        AND ORDER_PRODUCT_LISTS.ProdID = SecID;
                DELETE FROM ORDER_PRODUCT_LISTS
                    WHERE ORDER_PRODUCT_LISTS.OrdID = DesID
                        AND ORDER_PRODUCT_LISTS.ProdID = SecID;
                -- This is the very same logic as explained earlier.
                -- The only difference is that the characteristic of ORDER_PRODUCT_LISTS table's primary key
                -- is that of a composite key, thus using both element of the key to duplicate data.
            ELSE
                INSERT INTO DELETED_PRODLIST
                    SELECT * FROM ORDER_PRODUCT_LISTS
                    WHERE ORDER_PRODUCT_LISTS.OrdID = DesID;
                DELETE FROM ORDER_PRODUCT_LISTS
                    WHERE ORDER_PRODUCT_LISTS.OrdID = DesID;
            END IF;
        END CASE;
    END$
DELIMITER ;

--
-- create a procedure that shows all order in the specified time(date, month, or year)
--
DELIMITER $
CREATE PROCEDURE AllOrderInTime(DesYear YEAR, DesMonth INT, DesDay INT, DesOrdStatus VARCHAR(15))
-- DesYear means Designated Year
-- DesMonth means Designated Month
-- DesDay means Designated Day
-- DesOrdStatus means Designated Order Status
    BEGIN
        -- check if there is any specified status of each order
        IF DesOrdStatus IS NULL THEN
            -- check if the user want to specify the date or not(an entire month or year)
            IF DesDay IS NULL THEN
                -- check if the use wants to see orders in a month or an entire year
                IF DesMonth IS NULL THEN
                    -- show important data about each order which are Order's ID, customer's name,
                    -- driver's ID, total, and its current status
                    SELECT o.OrdID, c.CusName, o.DriverID, o.OrdTotal, o.OrdStatus
                        FROM ORDERS o, CUSTOMERS c
                        WHERE c.CusID = o.CusID
                            AND YEAR(o.TimeOfOrdering) = DesYear
                        ORDER BY o.OrdID;
                ELSE
                    SELECT o.OrdID, c.CusName, o.DriverID, o.OrdTotal, o.OrdStatus
                        FROM ORDERS o, CUSTOMERS c 
                        WHERE c.CusID = o.CusID
                            AND MONTH(o.TimeOfOrdering) = DesMonth
                            AND YEAR(o.TimeOfOrdering) = DesYear
                        ORDER BY o.OrdID;
                END IF;
            ELSE
                SELECT o.OrdID, c.CusName, o.DriverID, o.OrdTotal, o.OrdStatus
                    FROM ORDERS o, CUSTOMERS c
                    WHERE c.CusID = o.CusID
                        AND MONTH(o.TimeOfOrdering) = DesMonth
                        AND YEAR(o.TimeOfOrdering) = DesYear
                        AND DAY(o.TimeOfOrdering) = DesDay
                    ORDER BY o.OrdID;
            END IF;
        ELSE
            IF DesDay IS NULL THEN
                IF DesMonth IS NULL THEN
                    SELECT o.OrdID, c.CusName, o.DriverID, o.OrdTotal, o.OrdStatus
                        FROM ORDERS o, CUSTOMERS c
                        WHERE c.CusID = o.CusID
                            AND YEAR(o.TimeOfOrdering) = DesYear
                            AND o.OrdStatus = DesOrdStatus
                        ORDER BY o.OrdID;
                ELSE
                    SELECT o.OrdID, c.CusName, o.DriverID, o.OrdTotal, o.OrdStatus
                        FROM ORDERS o, CUSTOMERS c 
                        WHERE c.CusID = o.CusID
                            AND MONTH(o.TimeOfOrdering) = DesMonth
                            AND YEAR(o.TimeOfOrdering) = DesYear
                            AND o.OrdStatus = DesOrdStatus
                        ORDER BY o.OrdID;
                END IF;
            ELSE
                SELECT o.OrdID, c.CusName, o.DriverID, o.OrdTotal, o.OrdStatus
                    FROM ORDERS o, CUSTOMERS c
                    WHERE c.CusID = o.CusID
                        AND MONTH(o.TimeOfOrdering) = DesMonth
                        AND YEAR(o.TimeOfOrdering) = DesYear
                        AND DAY(o.TimeOfOrdering) = DesDay
                        AND o.OrdStatus = DesOrdStatus
                    ORDER BY o.OrdID;
            END IF;
        END IF;
    END $
DELIMITER ;

--
-- create a procedure that visulise the total revenue made in a specified time(date, month, or year)
--
DELIMITER $
CREATE PROCEDURE SummarisedTotal(DesYear YEAR, DesMonth INT, DesDay INT)
-- DesYear means Designated Year
-- DesMonth means Designated Month
-- DesDay means Designated Day
    BEGIN
        -- check if the user want to summarise in an extent of a day
        IF DesDay IS NULL THEN
            -- check to see if the user want to summarise the total amount of money made in a month or an entire year
            IF DesMonth IS NULL THEN
                -- show all the order ID, how many they are, and how much they made
                SELECT GROUP_CONCAT(OrdID,'|',DATE(TimeOfOrdering) SEPARATOR ' , '), COUNT(OrdID), SUM(OrdTotal)
                    FROM ORDERS
                    WHERE YEAR(TimeOfOrdering) = DesYear
                        -- The money made in an establishment can only come from complete orders.
                        -- Hence, the status of each order must sbe made sure to be COMPLETED
                        AND OrdStatus = 'COMPLETED';
            ELSE
                SELECT GROUP_CONCAT(OrdID,'|',DATE(TimeOfOrdering) SEPARATOR ' , '), COUNT(OrdID), SUM(OrdTotal)
                    FROM ORDERS
                    WHERE YEAR(TimeOfOrdering) = DesYear
                        AND MONTH(TimeOfOrdering) = DesMonth
                        AND OrdStatus = 'COMPLETED';
            END IF;
        ELSE
            SELECT GROUP_CONCAT(OrdID,'|',DATE(TimeOfOrdering) SEPARATOR ' , '), COUNT(OrdID), SUM(OrdTotal)
                    FROM ORDERS
                    WHERE YEAR(TimeOfOrdering) = DesYear
                        AND MONTH(TimeOfOrdering) = DesMonth
                        AND DAY(TimeOfOrdering) = DesDay
                        AND OrdStatus = 'COMPLETED';
        END IF;
    END$
DELIMITER ;

--
-- create a procedure that help assign an order to a driver
--
DELIMITER $
CREATE PROCEDURE AssignOrder(DesDriverID INT, DesOrdID INT)
-- DesDriverID means Designated Driver ID
-- DesOrdID means Designated Order ID
    BEGIN
        -- update the column "DriverID" on the table "ORDERS"
        UPDATE ORDERS
            SET DriverID = DesDriverID
            WHERE OrdID = DesOrdID;
    END$
DELIMITER ;

--
-- create a procedure that summarise total revenue made from a customer
-- users can also specify a customer
--
DELIMITER $
CREATE PROCEDURE SummaryByCustomer(DesYear YEAR, DesMonth INT, DesDay INT, DesCusID INT)
-- DesYear means Designated Year
-- DesMonth means Designated Month
-- DesDay means Designated Day
-- DesCusID means Designated Customer ID
    BEGIN
        -- check if the user has specified a customer for the visualisation
        IF DesCusID IS NULL THEN
            -- check if the day is specified
            IF DesDay IS NULL THEN
                -- check if the user wants to see statistics in a specified month or a year
                IF DesMonth IS NULL THEN
                    -- show Customer ID, Customer's name, number of orders made, 
                    -- and total revenue made from every "completed" order
                    SELECT c.CusID, c.CusName, COUNT(DISTINCT o.OrdID), SUM(o.OrdTotal)
                        FROM ORDERS o, CUSTOMERS c
                        WHERE o.CusID = c.CusID
                            AND YEAR(o.TimeOfOrdering) = DesYear
                            AND o.OrdStatus = 'COMPLETED'
                        GROUP BY o.CusID;
                ELSE
                    SELECT c.CusID, c.CusName, COUNT(DISTINCT o.OrdID), SUM(o.OrdTotal)
                        FROM ORDERS o, CUSTOMERS c
                        WHERE o.CusID = c.CusID
                            AND YEAR(o.TimeOfOrdering) = DesYear
                            AND MONTH(o.TimeOfOrdering) = DesMonth
                            AND o.OrdStatus = 'COMPLETED'
                        GROUP BY o.CusID;
                END IF;
            ELSE
                SELECT c.CusID, c.CusName, COUNT(DISTINCT o.OrdID), SUM(o.OrdTotal)
                    FROM ORDERS o, CUSTOMERS c
                    WHERE o.CusID = c.CusID
                        AND YEAR(o.TimeOfOrdering) = DesYear
                        AND MONTH(o.TimeOfOrdering) = DesMonth
                        AND DAY(o.TimeOfOrdering) = DesDay
                        AND o.OrdStatus = 'COMPLETED'
                    GROUP BY o.CusID;
            END IF;
        ELSE
            IF DesDay IS NULL THEN
                IF DesMonth IS NULL THEN
                    SELECT c.CusID, c.CusName, COUNT(DISTINCT o.OrdID), SUM(o.OrdTotal)
                    FROM ORDERS o, CUSTOMERS c
                    WHERE o.CusID = c.CusID
                        AND o.CusID = DesCusID
                        AND YEAR(o.TimeOfOrdering) = DesYear
                        AND o.OrdStatus = 'COMPLETED';
                ELSE
                    SELECT c.CusID, c.CusName, COUNT(DISTINCT o.OrdID), SUM(o.OrdTotal)
                    FROM ORDERS o, CUSTOMERS c
                    WHERE o.CusID = c.CusID
                        AND o.CusID = DesCusID
                        AND YEAR(o.TimeOfOrdering) = DesYear
                        AND MONTH(o.TimeOfOrdering) = DesMonth
                        AND o.OrdStatus = 'COMPLETED';
                END IF;
            ELSE
                SELECT c.CusID, c.CusName, COUNT(DISTINCT o.OrdID), SUM(o.OrdTotal)
                    FROM ORDERS o, CUSTOMERS c
                    WHERE o.CusID = c.CusID
                        AND o.CusID = DesCusID
                        AND YEAR(o.TimeOfOrdering) = DesYear
                        AND MONTH(o.TimeOfOrdering) = DesMonth
                        AND DAY(o.TimeOfOrdering) = DesDay
                        AND o.OrdStatus = 'COMPLETED';
            END IF;
        END IF;
    END$
DELIMITER ;

--
-- create a procedure that summarise total revenue made from a driver
-- users can also specify a customer
--
DELIMITER $
CREATE PROCEDURE DriverStatistics(DesYear YEAR, DesMonth INT, DesDay INT, DesDriverID INT)
-- DesYear means Designated Year
-- DesMonth means Designated Month
-- DesDay means Designated Day
-- DesDriverID means Designated Driver ID
    BEGIN
        -- check to see if the visualisation is to show statistics of a specified driver or not
        IF DesDriverID IS NULL THEN
            -- check to see if there is a specified day inserted as a parameter
            IF DesDay IS NULL THEN 
                -- check to see if the month was specified
                IF DesMonth IS NULL THEN
                    -- show Driver ID, Driver's Name, number of completed order, and total revenue made
                    SELECT d.DriverID, d.DriverName, COUNT(DISTINCT o.OrdID), SUM(o.OrdTotal)
                        FROM ORDERS o, DRIVERS d
                        WHERE o.DriverID = d.DriverID
                            AND YEAR(o.TimeOfOrdering) = DesYear
                            AND o.OrdStatus = 'COMPLETED'
                        GROUP BY o.DriverID;
                ELSE
                    SELECT d.DriverID, d.DriverName, COUNT(DISTINCT o.OrdID), SUM(o.OrdTotal)
                        FROM ORDERS o, DRIVERS d
                        WHERE o.DriverID = d.DriverID
                            AND YEAR(o.TimeOfOrdering) = DesYear
                            AND MONTH(o.TimeOfOrdering) = DesMonth
                            AND o.OrdStatus = 'COMPLETED'
                        GROUP BY o.DriverID;
                END IF;
            ELSE
                SELECT d.DriverID, d.DriverName, COUNT(DISTINCT o.OrdID), SUM(o.OrdTotal)
                    FROM ORDERS o, DRIVERS d
                    WHERE o.DriverID = d.DriverID
                        AND YEAR(o.TimeOfOrdering) = DesYear
                        AND MONTH(o.TimeOfOrdering) = DesMonth
                        AND DAY(o.TimeOfOrdering) = DesDay
                        AND o.OrdStatus = 'COMPLETED'
                    GROUP BY o.DriverID;
            END IF;
        ELSE
            IF DesDay IS NULL THEN
                IF DesMonth IS NULL THEN
                    SELECT d.DriverID, d.DriverName, COUNT(DISTINCT o.OrdID), SUM(o.OrdTotal)
                    FROM ORDERS o, DRIVERS d
                    WHERE o.DriverID = d.DriverID
                        AND o.DriverID = DesDriverID
                        AND YEAR(o.TimeOfOrdering) = DesYear
                        AND o.OrdStatus = 'COMPLETED';
                ELSE
                    SELECT d.DriverID, d.DriverName, COUNT(DISTINCT o.OrdID), SUM(o.OrdTotal)
                    FROM ORDERS o, DRIVERS d
                    WHERE o.DriverID = d.DriverID
                        AND o.DriverID = DesDriverID
                        AND YEAR(o.TimeOfOrdering) = DesYear
                        AND MONTH(o.TimeOfOrdering) = DesMonth
                        AND o.OrdStatus = 'COMPLETED';
                END IF;
            ELSE
                SELECT d.DriverID, d.DriverName, COUNT(DISTINCT o.OrdID), SUM(o.OrdTotal)
                    FROM ORDERS o, DRIVERS d
                    WHERE o.DriverID = d.DriverID
                        AND o.DriverID = DesDriverID
                        AND YEAR(o.TimeOfOrdering) = DesYear
                        AND MONTH(o.TimeOfOrdering) = DesMonth
                        AND DAY(o.TimeOfOrdering) = DesDay
                        AND o.OrdStatus = 'COMPLETED';
            END IF;
        END IF;
    END$
DELIMITER ;


----#### EXAMPLE DATA ####----

INSERT INTO PRODUCTS VALUES
    (1111, 'Lucky Soap', 25, 9999),
    (2222, 'Lucky Shampoo', 25, 25),
    (3333, 'Crisps Potato small', 5, 95),
    (4444, 'Crisps Potato big', 20, 65),
    (5555, 'Kola-Koca', 50, 125),
    (6666, 'Geremiah fountain pen', 4000, 1),
    (7777, 'Working gloves', 25, 62),
    (8888, 'Moonlight Dish soap', 10, 79),
    (9999, 'Windblow Excel detergent', 55, 655);
-- insert a set of imagined data into the table PRODUCTS

INSERT INTO DRIVERS VALUES
    (101, 'Worrapat Ashford', '06-4349-2356'),
    (202, 'Sujinat Jitwiriyanont', '99-9999-9999'),
    (303, 'Attapol Rutherford', '60-6060-6060'),
    (404, 'Not Found', 'xx-xxxx-xxxx'),
    (505, 'Harry Potters', '06-4856-4856');
-- insert a set of imagined data into the table DRIVERS

INSERT INTO CUSTOMERS VALUES
    (001, 'Vladimir Putin', 55.756496974, 37.623664172, '05-6567-4545', 'NotExKGBfrfr', "I'm at Lubyanka Building but I'm not an ex KGB bro"),
    (002, 'The one', 47.5074903, 11.0879396, '69-6969-6969', NULL, NULL),
    (003, 'Mochi', 13.739298067, 100.534061590, NULL, 'mochithechihuahua', 'woof'),
    (004, 'Somsak Apollo', 98.565658789, 2.659855548, '89-5656-7375', NULL, 'Room 2532, 25th floor, Kanye West Apartment');
-- insert a set of imagined data into the table CUSTOMERS

INSERT INTO ORDERS VALUES
    (1000, 001, NULL, NULL, '2023-01-01 00:00:00', NULL, DEFAULT),
    (1001, 002, NULL, NULL, '2023-02-14 16:00:25', '2023-09-30 07:30:00', DEFAULT),
    (1002, 003, NULL, NULL, '2023-05-15 11:12:59', '2023-12-25 00:00:00', DEFAULT);
INSERT INTO ORDER_PRODUCT_LISTS VALUES
    (1000, 3333, 10),
    (1000, 4444, 5),
    (1000, 5555, 5),
    (1001, 6666, 1),
    (1002, 1111, 2),
    (1002, 2222, 2),
    (1002, 8888, 3),
    (1002, 9999, 3);
/*
insert a set of imagined data into the table ORDERS and ORDER_PRODUCT_LISTS

Insertion on these tables must either happens simultaneously
or in an order of the ORDERS row inserted prior to that of the ORDER_PRODUCT_LISTS,
for their relationship is that of an order and its list of products.

UNDER NO CIRCUMSTANCE THAT THE PRODUCT LIST BE INSERTED BEFORE ITS RESPECTIVE ORDER.
*/