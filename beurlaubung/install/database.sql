CREATE TABLE `ext_beurlaubung_antrag`
(
    `id`              int(11) NOT NULL AUTO_INCREMENT,
    `status`   tinyint(1) NOT NULL DEFAULT '0',
    `createdTime`            int(11) NOT NULL,
    `userID`          int(11) NOT NULL,
    `datumStart`      date         NOT NULL,
    `datumEnde`       date         NOT NULL,
    `stunden`         varchar(255)   NOT NULL,
    `info`     text     NOT NULL,
    
    `doneKL`     tinyint(1) NOT NULL DEFAULT '-1',
    `doneKLDate` date DEFAULT NULL,
    `doneKLInfo`     text,
    `doneSL`     tinyint(1) NOT NULL DEFAULT '-1',
    `doneSLDate` date DEFAULT NULL,
    `doneSLInfo`     text,

    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;