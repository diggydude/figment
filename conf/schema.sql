DROP VIEW IF EXISTS `figment_message_display`, `figment_engagement_display`,
  `figment_profile_display`;

DROP TABLE IF EXISTS `figment_avatar`,  `figment_banner`,   `figment_color_scheme`,
  `figment_user`,    `figment_profile`, `figment_message`,  `figment_upload`,
  `figment_follow`,  `figment_mention`, `figment_hashtag`,  `figment_tagged`,
  `figment_like`,    `figment_dislike`, `figment_bookmark`, `figment_repost`,
  `figment_views`,   `figment_clicks`,  `figment_blocked`,  `figment_reason`,
  `figment_flagged`, `figment_banned`,  `figment_emoticon`, `figment_redirect`;

CREATE TABLE `figment_avatar` (
  `avatar_id` INT(11)     NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `filename`  VARCHAR(32) NOT NULL,
  UNIQUE KEY (`filename`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_banner` (
  `banner_id` INT(11)     NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `filename`  VARCHAR(32) NOT NULL,
  UNIQUE KEY (`filename`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_color_scheme` (
  `scheme_id`        INT(11) NOT NULL,
  `text_color`       VARCHAR(6) NOT NULL,
  `background_color` VARCHAR(6) NOT NULL,
  `highlight_color`  VARCHAR(6) NOT NULL,
  `shadow_color`     VARCHAR(6) NOT NULL,
  UNIQUE KEY (`text_color`, `background_color`, `highlight_color`, `shadow_color`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_user` (
  `user_id`      INT(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `username`     VARCHAR(32)  NOT NULL CHECK (CHAR_LENGTH(`username`) > 3),
  `password`     VARCHAR(32)  NOT NULL CHECK (CHAR_LENGTH(`password`) > 7),
  `question`     VARCHAR(255) NOT NULL,
  `answer`       VARCHAR(255) NOT NULL,
  `joined`       TIMESTAMP    NOT NULL DEFAULT NOW(),
  `is_admin`     TINYINT(1)   NOT NULL DEFAULT 0,
  `is_moderator` TINYINT(1)   NOT NULL DEFAULT 0,
  UNIQUE KEY (`username`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

INSERT INTO `figment_user` (`username`, `password`, `question`, `answer`, `is_admin`, `is_moderator`)
  VALUES ('sysop', 'figment', 'What is your username?', 'sysop', 1, 1);

CREATE TABLE `figment_profile` (
  `user_id`      INT(11)     NOT NULL,
  `display_name` VARCHAR(32) DEFAULT NULL,
  `biography`    TEXT        DEFAULT NULL,
  `birthday`     TIMESTAMP   DEFAULT NULL,
  `gender`       TINYINT(1)  DEFAULT NULL,
  `location`     VARCHAR(64) DEFAULT NULL,
  `avatar`       INT(11)     DEFAULT NULL,
  `banner`       INT(11)     DEFAULT NULL,
  `color_scheme` INT(11)     DEFAULT NULL,
  `pinned`       INT(11)     DEFAULT NULL,
  FOREIGN KEY (`user_id`)      REFERENCES `figment_user`         (`user_id`),
  FOREIGN KEY (`avatar`)       REFERENCES `figment_avatar`       (`avatar_id`),
  FOREIGN KEY (`banner`)       REFERENCES `figment_banner`       (`banner_id`),
  FOREIGN KEY (`color_scheme`) REFERENCES `figment_color_scheme` (`scheme_id`),
  FOREIGN KEY (`pinned`)       REFERENCES `figment_message`      (`message_id`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_message` (
  `message_id` INT(11)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `posted_by`  INT(11)   NOT NULL,
  `posted_at`  TIMESTAMP NOT NULL DEFAULT NOW(),
  `raw_text`   TEXT      NOT NULL CHECK (CHAR_LENGTH(`raw_text`) < 2049),
  `formatted`  TEXT      NOT NULL,
  FOREIGN KEY (`posted_by`) REFERENCES `figment_user` (`user_id`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_upload` (
  `upload_id` INT(11)     NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `filename`  VARCHAR(32) NOT NULL,
  UNIQUE KEY (`filename`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_follow` (
  `follower` INT(11) NOT NULL,
  `followed` INT(11) NOT NULL,
  UNIQUE KEY (`follower`, `followed`),
  FOREIGN KEY (`follower`) REFERENCES `figment_user` (`user_id`),
  FOREIGN KEY (`followed`) REFERENCES `figment_user` (`user_id`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_mention` (
  `mentioned`    INT(11) NOT NULL,
  `mentioned_in` INT(11) NOT NULL,
  UNIQUE KEY (`mentioned`, `mentioned_in`),
  FOREIGN KEY (`mentioned`)    REFERENCES `figment_user`    (`user_id`),
  FOREIGN KEY (`mentioned_in`) REFERENCES `figment_message` (`message_id`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_hashtag` (
  `hashtag_id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `content`    VARCHAR(255) NOT NULL,
  UNIQUE KEY (`content`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_tagged` (
  `message` INT(11) NOT NULL,
  `hashtag` INT(11) NOT NULL,
  UNIQUE KEY (`message`, `tagged`),
  FOREIGN KEY (`message`) REFERENCES `figment_message` (`message_id`),
  FOREIGN KEY (`hashtag`) REFERENCES `figment_hashtag` (`hashtag_id`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_like` (
  `message`  INT(11) NOT NULL,
  `liked_by` INT(11) NOT NULL,
  UNIQUE KEY (`message`, `liked_by`),
  FOREIGN KEY (`message`)  REFERENCES `figment_message` (`message_id`),
  FOREIGN KEY (`liked_by`) REFERENCES `figment_user`    (`user_id`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_dislike` (
  `message`     INT(11) NOT NULL,
  `disliked_by` INT(11) NOT NULL,
  UNIQUE KEY (`message`, `disliked_by`),
  FOREIGN KEY (`message`)     REFERENCES `figment_message` (`message_id`),
  FOREIGN KEY (`disliked_by`) REFERENCES `figment_user`    (`user_id`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_bookmark` (
  `message`       INT(11) NOT NULL,
  `bookmarked_by` INT(11) NOT NULL,
  UNIQUE KEY (`message`, `bookmarked_by`),
  FOREIGN KEY (`message`)       REFERENCES `figment_message` (`message_id`),
  FOREIGN KEY (`bookmarked_by`) REFERENCES `figment_user`    (`user_id`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_repost` (
  `original`    INT(11) NOT NULL,
  `reposted_in` INT(11) NOT NULL,
  UNIQUE KEY (`original`, `reposted_in`),
  FOREIGN KEY (`original`)    REFERENCES `figment_message` (`message_id`),
  FOREIGN KEY (`reposted_in`) REFERENCES `figment_message` (`message_id`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_reply` (
  `reply`    INT(11) NOT NULL,
  `reply_to` INT(11) NOT NULL,
  UNIQUE KEY (`reply`, `reply_to`),
  FOREIGN KEY (`reply`)    REFERENCES `figment_message` (`message_id`),
  FOREIGN KEY (`reply_to`) REFERENCES `figment_message` (`message_id`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_clicks` (
  `message` INT(11) NOT NULL,
  `clicks`  INT(11) NOT NULL DEFAULT 0,
  UNIQUE KEY (`message`),
  FOREIGN KEY (`message`) REFERENCES `figment_message` (`message_id`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_views` (
  `message` INT(11) NOT NULL,
  `views`   INT(11) NOT NULL DEFAULT 0,
  UNIQUE KEY (`message`),
  FOREIGN KEY (`message`) REFERENCES `figment_message` (`message_id`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_blocked` (
  `blocker` INT(11) NOT NULL,
  `blocked` INT(11) NOT NULL,
  UNIQUE KEY (`blocker`, `blocked`),
  FOREIGN KEY (`blocker`) REFERENCES `figment_user` (`user_id`),
  FOREIGN KEY (`blocked`) REFERENCES `figment_user` (`user_id`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_reason` (
  `reason_id` INT(11)     NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `text`      VARCAR(255) NOT NULL,
  UNIQUE KEY (`reason`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

INSERT INTO `figment_reason` (`text`) VALUES ('Posting child pornography.');
INSERT INTO `figment_reason` (`text`) VALUES ('Making violent threats.');
INSERT INTO `figment_reason` (`text`) VALUES ('Inciting violence or other illegal activity.');
INSERT INTO `figment_reason` (`text`) VALUES ('D0xxing.');
INSERT INTO `figment_reason` (`text`) VALUES ('Harassing another user.');

CREATE TABLE `figment_flagged` (
  `message`    INT(11)   NOT NULL,
  `flagged_by` INT(11)   NOT NULL,
  `flagged_at` TIMESTAMP NOT NULL DEFAULT NOW(),
  `reason`     INT(11)   NOT NULL,
  UNIQUE KEY (`message`, `flagged_by`),
  FOREIGN KEY (`message`)    REFERENCES `figment_message` (`message_id`),
  FOREIGN KEY (`flagged_by`) REFERENCES `figment_user`    (`user_id`),
  FOREIGN KEY (`reason`)     REFERENCES `figment_reason`  (`reason_id`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_banned` (
  `offender`  INT(11)   NOT NULL CHECK (`offender` > 1),
  `banned_by` INT(11)   NOT NULL,
  `banned_at` TIMESTAMP NOT NULL DEFAULT NOW(),
  `reason`    INT(11)   NOT NULL,
  FOREIGN KEY (`offender`)  REFERENCES `figment_user`   (`user_id`),
  FOREIGN KEY (`banned_by`) REFERENCES `figment_user`   (`user_id`),
  FOREIGN KEY (`reason`)    REFERENCES `figment_reason` (`reason_id`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_emoticon` (
  `emoticon_id` INT(11)     NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `code`        VARCHAR(32) NOT NULL,
  `filename`    VARCHAR(32) NOT NULL,
  UNIQUE KEY (`code`),
  UNIQUE KEY (`filename`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE TABLE `figment_redirect` (
  `uri`        TEXT        NOT NULL,
  `short_uri`  VARCHAR(32) NOT NULL,
  `in_message` INT(11)     NOT NULL,
  UNIQUE KEY (`uri`, `in_message`),
  UNIQUE KEY (`short_uri`),
  FOREIGN KEY (`in_message`) REFERENCES `figment_message` (`message_id`)
) ENGINE XtraDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE latin1_swedish_ci;

CREATE VIEW `figment_message_display` AS
  SELECT `msg`.`formatted`           AS `message`,
         `msg`.`pinned`              AS `pinned`,
         `msg`.`posted_at`           AS `postedAt`,
         `usr`.`username`            AS `postedBy`,
         `prf`.`display_name`        AS `displayName`,
         `avt`.`filename`            AS `avatar`,
         `rpt`.`reply_to`            AS `replyingToMessage`,
         `rpa`.`username`            AS `replyingToAuthor`,
         count(`lik`.`liked_by`)     AS `likes`,
         count(`dsl`.`disliked_by`)  AS `dislikes`,
         count(`rps`.`reposted_in`)  AS `reposts`,
         count(`rpl`.`reply`)        AS `replies`,
         count(`ylk`.`liked_by`)     AS `youLikedIt`,
         count(`yds`.`disliked_by`)  AS `youDislikedIt`,
         count(`ybm`.`bookmarked_by` AS `youBookmarkedIt`,
         count(`yrp`.`reposted_in`)  AS `youRepostedIt`
    FROM      `figment_message` AS `msg`
    LEFT JOIN `figment_user`    AS `usr` ON `usr`.`user_id`    = `msg`.`posted_by`
    LEFT JOIN `figment_profile` AS `prf` ON `prf`.`user_id`    = `usr`.`user_id`
    LEFT JOIN `figment_avatar`  AS `avt` ON `avt`.`avatar_id`  = `prf`.`avatar`
    LEFT JOIN `figment_reply`   AS `rpt` ON `rpt`.`reply`      = `msg`.`message_id`
    LEFT JOIN `figment_message` AS `rpm` ON `rpm`.`message_id` = `rpt`.`reply_to`
    LEFT JOIN `figment_user`    AS `rpa` ON `rpa`.`user_id`    = `rpt`.`posted_by`
    LEFT JOIN `figment_like`    AS `lik` ON `lik`.`message`    = `msg`.`message_id`
    LEFT JOIN `figment_dislike` AS `dsl` ON `dsl`.`message`    = `msg`.`message_id`
    LEFT JOIN `figment_repost`  AS `rps` ON `rps`.`original`   = `msg`.`message_id`
    LEFT JOIN `figment_reply`   AS `rpl` ON `rpl`.`reply_to`   = `msg`.`message_id`
    LEFT JOIN `figment_like`    AS `ylk` ON `ylk`.`message`    = `msg`.`message_id`   /* WHERE `ylk`.`liked_by`      = <curr_user_id> */
    LEFT JOIN `figment_dislike` AS `yds` ON `yds`.`message`    = `msg`.`message_id`   /* WHERE `yds`.`disliked_by`   = <curr_user_id> */
    LEFT JOIN `figment_bookmark`AS `ybm` ON `ybm`.`message_id` = `msg`.`message_id`   /* WHERE `ybm`.`bookmarked_by` = <curr_user_id> */
    LEFT JOIN `figment_repost`  AS `yrp` ON `yrp`.`message`    = `msg`.`message_id`
    LEFT JOIN `figment_message` AS `yri` ON `yri`.`message_id` = `yrp`.`reposted_in`; /* WHERE `yri`.`posted_by`     = <curr_user_id> */

CREATE VIEW `figment_engagement_display` AS
  SELECT `vue`.`views`                AS `views`,
         `clk`.`clicks`               AS `clicks`,
         count(`lik`.`liked_by`)      AS `likes`,
         count(`dsl`.`disliked_by`)   AS `dislikes`,
         count(`rps`.`reposted_in`)   AS `reposts`,
         count(`rpl`.`reply`)         AS `replies`,
         count(`bkm`.`bookmarked_by`) AS `bookmarks`
    FROM      `figment_message`  AS `msg`
    LEFT JOIN `figment_views`    AS `vue` ON `vue`.`message`  = `msg`.`message_id`
    LEFT JOIN `figment_clicks`   AS `clk` ON `clk`.`message`  = `msg`.`message_id`
    LEFT JOIN `figment_like`     AS `lik` ON `lik`.`message`  = `msg`.`message_id`
    LEFT JOIN `figment_dislike`  AS `dsl` ON `dsl`.`message`  = `msg`.`message_id`
    LEFT JOIN `figment_repost`   AS `rps` ON `rps`.`original` = `msg`.`message_id`
    LEFT JOIN `figment_reply`    AS `rpl` ON `rpl`.`reply_to` = `msg`.`message_id`
    LEFT JOIN `figment_bookmark` AS `bkm` ON `bkm`.`message`  = `msg`.`message_id`;

CREATE VIEW `figment_profile_display` AS
  SELECT `usr`.`username`            AS `username`,
         `usr`.`joined`              AS `joined`,
         `prf`.`display_name`        AS `displayName`,
         `prf`.`biography`           AS `biography`,
         `prf`.`birthday`            AS `birthday`,
         `prf`.`gender`              AS `gender`,
         `prf`.`location`            AS `location`,
         `avt`.`filename`            AS `avatar`,
         `ban`.`filename`            AS `banner`,
         `clr`.`text_color`          AS `textColor`,
         `clr`.`background_color`    AS `backgroundColor`,
         `clr`.`shadow_color`        AS `shadowColor`,
         `clr`.`highlight_color`     AS `highlightColor`,
         `prf`.`pinned`              AS `pinnedMessage`,
         count(`pst`.`message_id`)   AS `posts`,
         count(`rps`.`message_id`)   AS `reposts`,
         count(`rpl`.`message_id`)   AS `replies`,
         count(`men`.`mentioned_in`) AS `mentions`,
         count(`flr`.`follower`)     AS `followers`,
         count(`flg`.`followed`)     AS `following`,
         count(`blk`.`blocked_by`)   AS `haters`
  FROM      `figment_user`         AS `usr`
  LEFT JOIN `figment_profile`      AS `prf` ON `prf`.`user_id`     = `usr`.`user_id`
  LEFT JOIN `figment_avatar`       AS `avt` ON `avt`.`avatar_id`   = `prf`.`avatar`
  LEFT JOIN `figment_banner`       AS `ban` ON `ban`.`banner_id`   = `prf`.`banner`
  LEFT JOIN `figment_color_scheme` AS `clr` ON `clr`.`scheme_id`   = `prf`.`color_scheme`
  LEFT JOIN `figment_message`      AS `pst` ON `pst`.`posted_by`   = `usr`.`user_id`
  LEFT JOIN `figment_repost`       AS `rps` ON `rps`.`reposted_in` = `pst`.`message`
  LEFT JOIN `figment_reply`        AS `rpl` ON `rpl`.`reply`       = `pst`.`message`
  LEFT JOIN `figment_mention`      AS `men` ON `men`.`mentioned`   = `usr`.`user_id`
  LEFT JOIN `figment_follow`       AS `flr` ON `flr`.`followed`    = `usr`.`user_id`
  LEFT JOIN `figment_follow`       AS `flg` ON `flg`.`follower`    = `usr`.`user_id`
  LEFT JOIN `figment_blocked`      AS `blk` ON `blk`.`blocked`     = `usr`.`user_id`;

