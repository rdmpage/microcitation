#!/bin/sh

/usr/local/mysql/bin/mysqldump --user=root microcitation cites multilingual publications rdmpage sha1  > "/Users/rpage/Dropbox/Backups/mysql/microcitation_$(date +%Y-%m-%d).sql"
