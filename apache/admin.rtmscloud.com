<VirtualHost *:80>
	ServerAdmin webmaster@visionsystems.tv
	DocumentRoot /home/paulo/public_html/rtmscloud.com/admin

	ServerName admin.rtmscloud.com
	ServerAlias admin.rtsmcloud.com
	
	<Directory />
		Options FollowSymLinks
		AllowOverride None
	</Directory>
	<Directory /home/paulo/public_html/rtmscloud.com/>
		Options Indexes FollowSymLinks MultiViews
		AllowOverride None
		Order allow,deny
		allow from all
	</Directory>

	ScriptAlias /cgi-bin/ /usr/lib/cgi-bin/
	<Directory "/usr/lib/cgi-bin">
		AllowOverride None
		Options +ExecCGI -MultiViews +SymLinksIfOwnerMatch
		Order allow,deny
		Allow from all
	</Directory>

	ErrorLog ${APACHE_LOG_DIR}/rtmscloud-admin-error.log

	# Possible values include: debug, info, notice, warn, error, crit,
	# alert, emerg.
	LogLevel warn

	CustomLog ${APACHE_LOG_DIR}/rtmscloud-admin-access.log combined
</VirtualHost>
