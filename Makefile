FTP=ftp://192.168.1.10
AUTH=-u root,root
REMOTE_DIR=cd /var/www/modules/
TARGET=tsclient

deploy:
	lftp $(AUTH) -e "$(REMOTE_DIR); rm -r $(TARGET)/; mirror -R $(TARGET)/ $(TARGET); bye" $(FTP)

restore:
	cd tsclient_old_version/ && lftp $(AUTH) -e "$(REMOTE_DIR); rm -r $(TARGET)/; mirror -R $(TARGET)/ $(TARGET)/; bye" $(FTP)
