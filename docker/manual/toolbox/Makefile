install: configure

configure:
	@echo "⚙️ Configuring Wordpress parameters..."
	wp core install \
		--url=${WORDPRESS_WEBSITE_URL_WITHOUT_HTTP} \
		--title=$(WORDPRESS_WEBSITE_TITLE) \
		--admin_user=${WORDPRESS_ADMIN_USER} \
		--admin_password=${WORDPRESS_ADMIN_PASSWORD} \
		--admin_email=${WORDPRESS_ADMIN_EMAIL}

	wp option update siteurl ${WORDPRESS_WEBSITE_URL}
	wp rewrite structure $(WORDPRESS_WEBSITE_POST_URL_STRUCTURE)
	wp plugin install ${WORDPRESS_PLUGIN_1}
	wp plugin activate ${WORDPRESS_PLUGIN_1}
	wp plugin activate ${WORDPRESS_PLUGIN_4}
	wp plugin activate ${WORDPRESS_PLUGIN_5}
	wp plugin delete ${WORDPRESS_PLUGIN_2}
	wp plugin delete ${WORDPRESS_PLUGIN_3}

