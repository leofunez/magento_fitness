# HELPERS

## Magento

### Run
`bin/start`

### Upgrade
`bin/cli bin/magento setup:upgrade`

### Compile
`bin/cli bin/magento setup:di:compile`

### Clean cache
`bin/cli bin/magento cache:clean`

`bin/cli bin/magento cache:flush`

### Deploy static files
`bin/cli php bin/magento setup:static-content:deploy -f`

### Clear Static File Cache
This removes old, generated versions of your files:

`bin/cli php bin/magento cache:clean static_file_di`

### Errors
`cd var report`
`vim error_hash`

### Reindex categories
1. Navigate to Catalog/Categories
2. Select the category
3. Display settings
4. Display mode
5. Change the value from the default (Products Only or Static Block Only) to Static Block and Products

### Reindex the catalog
`bin/cli bin/magento cache:clean`

`bin/cli bin/magento indexer:reindex`

### Developer Mode
`bin/cli php bin/magento deploy:mode:show`

`bin/cli php bin/magento deploy:mode:set developer`

`bin/cli php bin/magento deploy:mode:set production`

### Set admin theme via CLI
bin/cli php bin/magento config:set design/theme/adminhtml Vendor/CustomAdmin

### Delete cache files
`rm -rf src/pub/static/* src/var/view_preprocessed/* src/var/cache/* src/var/page_cache/* src/generated/*`

### Check auth file
`cat ~/.composer/auth.json`

### See all ports
`sudo lsof -i :80`

### Kill ports
`sudo kill -9 <ID>`

### Kill Apache and Nginx 80 port processes
`sudo systemctl stop apache2`

`sudo systemctl stop nginx`

## Docker
#### 1. Check running containers
`docker ps`

#### 2. Check all containers
`docker ps -a`

#### 3. Stop all running containers
`docker stop $(docker ps -a) -> docker rm -f $(docker ps -aq)`

#### 4. Docker Remove all containers
`docker volume prune -f`

`docker network prune -f`

`docker system prune -a --volumes -f`


## SQL
#### 1. Set manually the admin theme via SQL

`INSERT INTO core_config_data (scope, scope_id, path, value)
VALUES ('default', 0, 'design/theme/adminhtml', 'Vendor/CustomAdmin')
ON DUPLICATE KEY UPDATE value = 'Vendor/CustomAdmin';`