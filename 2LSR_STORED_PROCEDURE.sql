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
                SELECT GROUP_CONCAT(OrdID,'|',DATE(TimeOfOrdering) SEPARATOR ' , ') AS Orders, COUNT(OrdID) AS OrderCount, SUM(OrdTotal) AS Revenue
                    FROM ORDERS
                    WHERE YEAR(TimeOfOrdering) = DesYear
                        -- The money made in an establishment can only come from complete orders.
                        -- Hence, the status of each order must sbe made sure to be COMPLETED
                        AND OrdStatus = 'COMPLETED';
            ELSE
                SELECT GROUP_CONCAT(OrdID,'|',DATE(TimeOfOrdering) SEPARATOR ' , ') AS Orders, COUNT(OrdID) AS OrderCount, SUM(OrdTotal) AS Revenue
                    FROM ORDERS
                    WHERE YEAR(TimeOfOrdering) = DesYear
                        AND MONTH(TimeOfOrdering) = DesMonth
                        AND OrdStatus = 'COMPLETED';
            END IF;
        ELSE
            SELECT GROUP_CONCAT(OrdID,'|',DATE(TimeOfOrdering) SEPARATOR ' , ') AS Orders, COUNT(OrdID) AS OrderCount, SUM(OrdTotal) AS Revenue
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
                    SELECT c.CusID, c.CusName, COUNT(DISTINCT o.OrdID) AS OrderCount, SUM(o.OrdTotal) AS Revenue
                        FROM ORDERS o, CUSTOMERS c
                        WHERE o.CusID = c.CusID
                            AND YEAR(o.TimeOfOrdering) = DesYear
                            AND o.OrdStatus = 'COMPLETED'
                        GROUP BY o.CusID;
                ELSE
                    SELECT c.CusID, c.CusName, COUNT(DISTINCT o.OrdID) AS OrderCount, SUM(o.OrdTotal) AS Revenue
                        FROM ORDERS o, CUSTOMERS c
                        WHERE o.CusID = c.CusID
                            AND YEAR(o.TimeOfOrdering) = DesYear
                            AND MONTH(o.TimeOfOrdering) = DesMonth
                            AND o.OrdStatus = 'COMPLETED'
                        GROUP BY o.CusID;
                END IF;
            ELSE
                SELECT c.CusID, c.CusName, COUNT(DISTINCT o.OrdID) AS OrderCount, SUM(o.OrdTotal) AS Revenue
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
                    SELECT c.CusID, c.CusName, COUNT(DISTINCT o.OrdID) AS OrderCount, SUM(o.OrdTotal) AS Revenue
                    FROM ORDERS o, CUSTOMERS c
                    WHERE o.CusID = c.CusID
                        AND o.CusID = DesCusID
                        AND YEAR(o.TimeOfOrdering) = DesYear
                        AND o.OrdStatus = 'COMPLETED';
                ELSE
                    SELECT c.CusID, c.CusName, COUNT(DISTINCT o.OrdID) AS OrderCount, SUM(o.OrdTotal) AS Revenue
                    FROM ORDERS o, CUSTOMERS c
                    WHERE o.CusID = c.CusID
                        AND o.CusID = DesCusID
                        AND YEAR(o.TimeOfOrdering) = DesYear
                        AND MONTH(o.TimeOfOrdering) = DesMonth
                        AND o.OrdStatus = 'COMPLETED';
                END IF;
            ELSE
                SELECT c.CusID, c.CusName, COUNT(DISTINCT o.OrdID) AS OrderCount, SUM(o.OrdTotal) AS Revenue
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
                    SELECT d.DriverID, d.DriverName, COUNT(DISTINCT o.OrdID) AS OrderCount, SUM(o.OrdTotal) AS Revenue
                        FROM ORDERS o, DRIVERS d
                        WHERE o.DriverID = d.DriverID
                            AND YEAR(o.TimeOfOrdering) = DesYear
                            AND o.OrdStatus = 'COMPLETED'
                        GROUP BY o.DriverID;
                ELSE
                    SELECT d.DriverID, d.DriverName, COUNT(DISTINCT o.OrdID) AS OrderCount, SUM(o.OrdTotal) AS Revenue
                        FROM ORDERS o, DRIVERS d
                        WHERE o.DriverID = d.DriverID
                            AND YEAR(o.TimeOfOrdering) = DesYear
                            AND MONTH(o.TimeOfOrdering) = DesMonth
                            AND o.OrdStatus = 'COMPLETED'
                        GROUP BY o.DriverID;
                END IF;
            ELSE
                SELECT d.DriverID, d.DriverName, COUNT(DISTINCT o.OrdID) AS OrderCount, SUM(o.OrdTotal) AS Revenue
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
                    SELECT d.DriverID, d.DriverName, COUNT(DISTINCT o.OrdID) AS OrderCount, SUM(o.OrdTotal) AS Revenue
                    FROM ORDERS o, DRIVERS d
                    WHERE o.DriverID = d.DriverID
                        AND o.DriverID = DesDriverID
                        AND YEAR(o.TimeOfOrdering) = DesYear
                        AND o.OrdStatus = 'COMPLETED';
                ELSE
                    SELECT d.DriverID, d.DriverName, COUNT(DISTINCT o.OrdID) AS OrderCount, SUM(o.OrdTotal) AS Revenue
                    FROM ORDERS o, DRIVERS d
                    WHERE o.DriverID = d.DriverID
                        AND o.DriverID = DesDriverID
                        AND YEAR(o.TimeOfOrdering) = DesYear
                        AND MONTH(o.TimeOfOrdering) = DesMonth
                        AND o.OrdStatus = 'COMPLETED';
                END IF;
            ELSE
                SELECT d.DriverID, d.DriverName, COUNT(DISTINCT o.OrdID) AS OrderCount, SUM(o.OrdTotal) AS Revenue
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