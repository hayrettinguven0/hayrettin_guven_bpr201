create database guvenmedya;
use guvenmedya;

/*Üyeler*/
create table uyeler(
id int auto_increment primary key,
kullanici_adi varchar(50) not null unique,
email varchar(255) not null unique,
sifre varchar (255) not null,
kayit_tarihi datetime default now()
);

/*Konular*/
create table konular(
id int auto_increment primary key,
baslik varchar(255) not null,
olusturan_id int not null,
olusturma_tarihi datetime default now()
);

/*Yorumlar*/
create table yorumlar(
id int auto_increment primary key,
konu_id int not null,
yazar_id int not null,
icerik text not null,
yazma_tarihi datetime default now()
);

/* Üye mi Admin mi olması için uyeler tablosuna ekleme yapıyoruz*/
alter table uyeler
add column kullanici_tipi int default 0; # Herkes normal üye olarak kayıt olacak, biz veritabanından admin seçeceğiz.

/*Konu Beğenme*/
create table konu_begenileri (
id int auto_increment primary key,
konu_id int not null,
uye_id int not null,
begeni_tarihi datetime default now(),
unique key benzersiz_begeni (konu_id,uye_id) #Her üye sadece bir beğeni atabilir.
);

/*Yorum Beğenileri*/
create table yorum_begenileri(
id int auto_increment primary key,
yorum_id int not null,
uye_id int not null,
begeni_tarihi datetime default now(),
unique key benzersiz_yorum_begeni (yorum_id, uye_id) #Aynı şekilde her üye bir yorum beğenebilir. 
);
/*İçerik sabitleme işlemi oldu mu olmadı mı kontrolunü ekliyoruz */
alter table konular add column sabitlendi_mi int default 0;

/*Etiketler arası bağlantı kurma, referans*/
alter table konular add foreign key (olusturan_id) references uyeler(id);
alter table yorumlar add foreign key (yazar_id) references uyeler(id);
alter table yorumlar add foreign key (konu_id) references konular(id);
alter table konu_begenileri add foreign key (uye_id) references uyeler(id);
alter table konu_begenileri add foreign key (konu_id) references konular(id);
alter table yorum_begenileri add foreign key (uye_id) referenceS uyeler(id);
alter table yorum_begenileri add foreign key (yorum_id) references yorumlar(id);
/*-----------------------SABİT İKONU İÇİN TEEST AMAÇLI-----------------------------------*/
select * from konular;
select * from uyeler;
select * from yorumlar;

/* Admin Yetkisi Verme*/
update uyeler set kullanici_tipi = 1 where id = 1;

/*Konu Etiketleri*/
create table etiketler(
id int auto_increment primary key,
etiket_adi varchar(50) not null unique
);

create table konu_etiketleri(
id int auto_increment primary key,
konu_id int not null,
etiket_id int not null,
unique key benzersiz_konu_etiket (konu_id, etiket_id)
);
alter table konu_etiketleri add foreign key (konu_id) references konular(id) on delete cascade;
alter table konu_etiketleri add foreign key (etiket_id) references etiketler(id) on delete cascade;  



