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