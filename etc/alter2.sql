# Changes for Android/IOS app
# DB changes since last release FLY_1.3_HFL_5Oct2020

ALTER TABLE Members ADD COLUMN appLogin CHAR(60) NOT NULL DEFAULT '' AFTER keepLogin;

