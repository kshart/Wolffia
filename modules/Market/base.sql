CREATE TABLE market_item (
	id int not null auto_increment,
	name varchar(255),
	categoryIDs int,
	mainImg int,
	propertys varchar(2048)
)
CREATE TABLE market_category (
	id int not null auto_increment,
	parentID int,
	name varchar(255),
	PRIMARY KEY(id)
);