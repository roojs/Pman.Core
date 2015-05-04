
ALTER TABLE  core_notify_recur  ADD COLUMN campaign_id INT(11)  NOT NULL DEFAULT 0;

ALTER TABLE  core_notify_recur  ADD COLUMN keyword_filters TEXT  NOT NULL DEFAULT '';