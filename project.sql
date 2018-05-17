DROP VIEW view_display;
DROP TABLE sensor_groups;
DROP TABLE graph_items;
DROP TABLE graph_master;
DROP TABLE sensor_log;
DROP TABLE sensor_current;
DROP TABLE sensor_setup;
DROP TABLE users;

CREATE TABLE users (
	userid serial PRIMARY KEY,
	username varchar unique not null,
	password varchar not null,
	email varchar not null,
	superadmin boolean default false
);
INSERT INTO users VALUES (default,'paulo','sbob','paulo@visionsystems.tv',true);

CREATE TABLE sensor_setup (
	mac char(14) unique not null,
	owner integer not null,
	description varchar default null,
	water_min integer default 0,
	water_max integer default 1000,
	clickspergal integer default 100,
	electric_min integer default 0,
	electric_max integer default 500,
	rmsadjust numeric default 8.2377, 
	temp1_min integer default 100,
	temp1_max integer default 150,
	temp2_min integer default 100,
	temp2_max integer default 150,
	temp3_min integer default 100,
	temp3_max integer default 150,
	temp4_min integer default 100,
	temp4_max integer default 150,
	temp5_min integer default 100,
	temp5_max integer default 100,
	temp6_min integer default 150,
	temp6_max integer default 150,
	temp1_lbl varchar,
	temp2_lbl varchar,
	temp3_lbl varchar,
	temp4_lbl varchar,
	temp5_lbl varchar,
	temp6_lbl varchar,
	tog1 boolean default true,
	tog2 boolean default true,
	tog3 boolean default true,
	tog4 boolean default true,
	tog5 boolean default true,
	tog6 boolean default true,
	tog1_lbl varchar,
	tog2_lbl varchar,
	tog3_lbl varchar,
	tog4_lbl varchar,
	tog5_lbl varchar,
	tog6_lbl varchar,
	sensor_group int
);
INSERT INTO sensor_setup (mac,owner,description) VALUES ('abcd.abcd.abcd',1,'2000 6th St SE, Minot ND');
ALTER TABLE sensor_setup ADD CONSTRAINT fk_owner FOREIGN KEY (owner) REFERENCES users(userid);

CREATE TABLE sensor_current (
	mac char(14) not null,
	water integer,
	electric integer,
	temp1 integer,
	temp2 integer,
	temp3 integer,
	temp4 integer,
	temp5 integer,
	temp6 integer,
	tog1 boolean,
	tog2 boolean,
	tog3 boolean,
	tog4 boolean,
	tog5 boolean,
	tog6 boolean,
	lastip varchar(15),
	lastcontact timestamp
);

ALTER TABLE sensor_current ADD constraint fk_mac FOREIGN KEY (mac) REFERENCES sensor_setup(mac);
ALTER TABLE sensor_current ADD constraint uniq_mac UNIQUE (mac);
ALTER TABLE sensor_current ADD PRIMARY KEY (mac);
DELETE FROM sensor_current;

CREATE TABLE sensor_log AS select * FROM sensor_current;
ALTER TABLE sensor_log ADD constraint fk_mac FOREIGN KEY (mac) REFERENCES sensor_setup(mac);

CREATE view view_display AS select set.description,set.owner,set.sensor_group,cur.* FROM sensor_setup as set JOIN sensor_current as cur ON set.mac=cur.mac;

CREATE TABLE graph_master (
	id serial PRIMARY KEY,
	sortorder integer not null default 1,
	mac char(14) not null,
	h_lbl varchar,
	v_lbl varchar,
	width integer default 400,
	height integer default 100,
	timeframe varchar(6) default '1d',
	onoverview boolean default false
);
CREATE TABLE graph_items (
	id serial PRIMARY KEY,
	graphid integer,
	col_name varchar not null,
	col_lbl varchar not null,
	color varchar(7) default '#000000',
	type varchar not null default 'LINE1'
);
ALTER TABLE graph_master ADD constraint fk_mac FOREIGN KEY (mac) REFERENCES sensor_setup(mac);
ALTER TABLE graph_items ADD constraint fk_id FOREIGN KEY (graphid) REFERENCES graph_master(id);
CREATE TABLE sensor_groups (
	id serial primary key,
	owner_id int,
	group_name varchar,
	immutable bool default false
);
ALTER TABLE sensor_groups ADD constraint fk_ownerid FOREIGN KEY (owner_id) REFERENCES users(id);
INSERT INTO sensor_groups VALUES (default,1,'All',true);
INSERT INTO sensor_groups VALUES (default,1,'Des Moines',false);
INSERT INTO sensor_groups VALUES (default,1,'Minot',false);


ALTER table sensor_setup ADD constraint fk_sensor_group FOREIGN KEY (sensor_group) REFERENCES sensor_groups(id);


CREATE TABLE email_alerts (
	id serial PRIMARY KEY,
	mac char(14) not null,
	email varchar,
	active boolean default true,
	sensor_down boolean default true,
	water boolean default false,
	electric boolean default false,
	temp1 boolean default false,
	temp2 boolean default false,
	temp3 boolean default false,
	temp4 boolean default false,
	temp5 boolean default false,
	temp6 boolean default false,
	tog1 boolean default false,
	tog2 boolean default false,
	tog3 boolean default false,
	tog4 boolean default false,
	tog5 boolean default false,
	tog6 boolean default false
);
ALTER TABLE email_alerts ADD CONSTRAINT fk_mac FOREIGN KEY (mac) REFERENCES sensor_setup(mac);


