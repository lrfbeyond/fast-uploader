/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : lrfbeyond_demo

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2020-04-23 17:30:15
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for hw_file
-- ----------------------------
DROP TABLE IF EXISTS `hw_file`;
CREATE TABLE `hw_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL COMMENT '文件名',
  `filesize` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小',
  `md5` varchar(32) NOT NULL COMMENT '文件md5',
  `type` varchar(10) NOT NULL COMMENT '文件类型',
  `filepath` varchar(128) NOT NULL COMMENT '文件保存路径',
  `created_at` datetime NOT NULL COMMENT '上传时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4;
