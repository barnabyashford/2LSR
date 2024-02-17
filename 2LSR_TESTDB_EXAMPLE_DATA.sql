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
or in an order of the ORDERS row inserted prior to the PRODUCT_LISTS ROW,
for their relationship is that of an order and its list of products.

UNDER NO CIRCUMSTANCE THAT THE PRODUCT LIST BE INSERTED BEFORE ITS RESPECTIVE ORDER.
*/