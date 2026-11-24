CREATE TABLE IF NOT EXISTS "#__ottomo_email_campaigns" (
  "id" SERIAL PRIMARY KEY,
  "subject" varchar(255) NOT NULL DEFAULT '',
  "preheader" varchar(255) NOT NULL DEFAULT '',
  "senderName" varchar(255) NOT NULL DEFAULT '',
  "senderEmail" varchar(255) NOT NULL DEFAULT '',
  "plain" text NOT NULL DEFAULT '',
  "template" integer NOT NULL DEFAULT 0,
  "published" smallint NOT NULL DEFAULT 0,
  "sent" date DEFAULT NULL,
  "snapshot" text NOT NULL DEFAULT ''
);
CREATE INDEX IF NOT EXISTS "#__ottomo_email_campaigns_idx_published_sent" ON "#__ottomo_email_campaigns" ("published", "sent");

CREATE TABLE IF NOT EXISTS "#__ottomo_email_campaigns_data" (
  "id" SERIAL PRIMARY KEY,
  "campaignID" integer NOT NULL DEFAULT 0,
  "position" varchar(255) NOT NULL DEFAULT '',
  "content" text NOT NULL DEFAULT ''
);
CREATE INDEX IF NOT EXISTS "#__ottomo_email_campaigns_data_idx_campaignID" ON "#__ottomo_email_campaigns_data" ("campaignID");

CREATE TABLE IF NOT EXISTS "#__ottomo_email_campaigns_xref" (
  "topicID" integer NOT NULL,
  "campaignID" integer NOT NULL,
  PRIMARY KEY ("topicID", "campaignID")
);
CREATE INDEX IF NOT EXISTS "#__ottomo_email_campaigns_xref_idx_campaignID" ON "#__ottomo_email_campaigns_xref" ("campaignID");

CREATE TABLE IF NOT EXISTS "#__ottomo_statistics" (
  "id" SERIAL PRIMARY KEY,
  "typeID" integer NOT NULL DEFAULT 0,
  "campaignID" integer NOT NULL DEFAULT 0,
  "subscriberID" integer NOT NULL DEFAULT 0,
  "date" date NOT NULL,
  "time" time NOT NULL,
  "link" varchar(2048) NOT NULL DEFAULT ''
);
CREATE INDEX IF NOT EXISTS "#__ottomo_statistics_idx_campaign" ON "#__ottomo_statistics" ("campaignID", "typeID", "subscriberID");
CREATE INDEX IF NOT EXISTS "#__ottomo_statistics_idx_type_date" ON "#__ottomo_statistics" ("typeID", "date");
CREATE INDEX IF NOT EXISTS "#__ottomo_statistics_idx_subscriberID" ON "#__ottomo_statistics" ("subscriberID");

CREATE TABLE IF NOT EXISTS "#__ottomo_statistics_types" (
  "id" SERIAL PRIMARY KEY,
  "type" varchar(255) NOT NULL DEFAULT ''
);

CREATE TABLE IF NOT EXISTS "#__ottomo_subscribers" (
  "id" SERIAL PRIMARY KEY,
  "status" smallint NOT NULL DEFAULT 0,
  "activation" varchar(100) NOT NULL DEFAULT '',
  "name" varchar(255) NOT NULL DEFAULT '',
  "email" varchar(255) NOT NULL UNIQUE,
  "address" varchar(255) NOT NULL DEFAULT '',
  "city" varchar(255) NOT NULL DEFAULT '',
  "state" varchar(255) NOT NULL DEFAULT '',
  "zip" varchar(255) NOT NULL DEFAULT '',
  "country" varchar(255) NOT NULL DEFAULT '',
  "phone" varchar(255) NOT NULL DEFAULT '',
  "mobile" varchar(255) NOT NULL DEFAULT '',
  "fax" varchar(255) NOT NULL DEFAULT '',
  "birthday" date DEFAULT NULL,
  "company" varchar(255) NOT NULL DEFAULT '',
  "position" varchar(255) NOT NULL DEFAULT '',
  "vat" varchar(255) NOT NULL DEFAULT '',
  "tax" varchar(255) NOT NULL DEFAULT '',
  "created" timestamp without time zone DEFAULT NULL
);
CREATE INDEX IF NOT EXISTS "#__ottomo_subscribers_idx_status" ON "#__ottomo_subscribers" ("status");
CREATE INDEX IF NOT EXISTS "#__ottomo_subscribers_idx_activation" ON "#__ottomo_subscribers" ("activation");

CREATE TABLE IF NOT EXISTS "#__ottomo_subscribers_xref" (
  "topicID" integer NOT NULL,
  "subscriberID" integer NOT NULL,
  PRIMARY KEY ("topicID", "subscriberID")
);
CREATE INDEX IF NOT EXISTS "#__ottomo_subscribers_xref_idx_subscriberID" ON "#__ottomo_subscribers_xref" ("subscriberID");

CREATE TABLE IF NOT EXISTS "#__ottomo_templates" (
  "id" SERIAL PRIMARY KEY,
  "name" varchar(255) NOT NULL DEFAULT '',
  "folder" varchar(255) NOT NULL UNIQUE,
  "manifest" text NOT NULL DEFAULT ''
);

CREATE TABLE IF NOT EXISTS "#__ottomo_topics" (
  "id" SERIAL PRIMARY KEY,
  "name" varchar(255) NOT NULL DEFAULT '',
  "published" smallint NOT NULL DEFAULT 0,
  "private" smallint NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS "#__ottomo_topics_idx_published_name" ON "#__ottomo_topics" ("published", "name");
