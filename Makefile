deploy:
	rm tsclient.tar
	tar -C www/modules/ -cf tsclient.tar tsclient
	@lftp -u root,root -e "cd /var/www/modules/; put tsclient.tar; rm -r tsclient/; tar -xf archive.tar; rm tsclient.tar; bye" ftp://192.168.1.10
restore:
	@lftp -u root,root -e "cd /var/www/modules/; put tsclientOld.tar; rm -r tsclient/; tar -xf tsclientOld.tar; rm tsclientOld.tar; bye" ftp://192.168.1.10