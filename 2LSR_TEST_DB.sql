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
