- all prices / costs defaults to 0

# categories
name

# products 
name
category_id
barcode
price
cost
min_stock
avg_purchase_quantity
type -> string in db but casted php enum (Raw , Manufactured)
unit -> string in db but casted php enum

# suppliers
name
phone nullable

# consumer
name
phone nullable

# inventory
product_id
quantity

# raw material entrance -> (has many items)
user_id
supplier_id
status -> string in db but casted php enum (Draft , Closed)
total
closed_at
-- after closed : it increase the inventory

## raw material entrance items
product_id
quantity
price
total


# raw material out -> (has many items)
user_id
consumer_id
status -> string in db but casted php enum (Draft , Closed)
total
closed_at
-- after closed : it decreases the inventory

## raw material out items
product_id
quantity
price
total


# manufactured material entrance -> (has many items)
user_id
supplier_id
status -> string in db but casted php enum (Draft , Closed)
total
closed_at
-- after closed : it increase the inventory

## manufactured material entrance items
product_id
quantity
price
total

# manufactured material out -> (has many items)
user_id
consumer_id
status -> string in db but casted php enum (Draft , Closed)
total
closed_at
-- after closed : it decreases the inventory

## manufactured material out items
product_id
quantity
price
total
