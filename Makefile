deploy:
	cd www/modules/
	lftp -u root,root -e "cd /var/www/modules/; rm -r tsclient/; mirror -R tsclient/ tsclient; bye" ftp://192.168.1.10
	cd ../../
restore:
	cd tsclient_old_version/
	lftp -u root,root -e "cd /var/www/modules/; rm -r tsclient/; mirror -R tsclient/ tsclient/; bye" ftp://192.168.1.10
