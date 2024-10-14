# Project_Sorties
Projet Sorties pour l'ENI

##
PHP 8.2.18

##
Symfony 6.4

##
Bootstrap 5.3

Bootswatch Theme: [Vapor](https://bootswatch.com/vapor/)

##
MySQL 8.3.

MariaDB 11.3.2

Table Trip {  
tri_id integer [primary key]  
tri_name varchar (30)  
tri_starting_date date  
tri_duration integer  
tri_closing_date date  
tri_max_inscription_number integer  
tri_description text  
tri_cancellation_reason text  
tri_state integer  
tri_location integer  
tri_organiser integer  
tri_site integer  
}  

Table Location {  
loc_id integer [primary key]  
loc_name varchar (30)  
loc_street varchar (30)  
loc_latitude float  
loc_longitude float  
loc_city integer  
}  

Table City {  
cit_id integer [primary key]  
cit_name varchar (30)  
cit_post_code varchar (5)  
}  

Table Site {  
sit_id integer [primary key]  
sit_name varchar (30)  
}  

Table Participant {  
par_id integer [primary key]  
par_username varchar (30)  
par_password varchar (50)  
par_last_name varchar (30)  
par_first_name varchar (30)  
par_phone varchar (15)  
par_email varchar (10)  
par_picture varchar (50)  
par_is_admin boolean  
par_is_active boolean  
par_site integer  
}  

Table State {  
sta_id integer [primary key]  
sta_label varchar (30)  
}  

Table Subscribe {  
sub_participant_id integer [primary key]  
sub_trip_id integer [primary key]  
}  

Ref: Trip.tri_location > Location.loc_id  

Ref: Location.loc_city > City.cit_id  

Ref: Trip.tri_state > State.sta_id  

Ref: Trip.tri_site > Site.sit_id  

Ref: Trip.tri_organiser > Participant.par_id  

Ref: Trip.tri_id > Subscribe.sub_trip_id  

Ref: Participant.par_id > Subscribe.sub_participant_id  

Ref: Participant.par_site > Site.sit_id  