## INSTALL
#### 1. Clone the project:

#### 1.1 Automated Setup

`curl -s https://raw.githubusercontent.com/markshust/docker-magento/master/lib/onelinesetup | bash -s -- magento.test community 2.4.8
`

###### *With this option I couldn't install the sample data.

#### 1.2 Manual Setup (recommended)

##### 1.2.1. Download the Docker Compose template:

`curl -s https://raw.githubusercontent.com/markshust/docker-magento/master/lib/template | bash`

##### 1.2.2. Download the version of Magento

`bin/download community 2.4.8`

##### 1.2.3. Run the setup installer for Magento:
`bin/setup magento.test`

Navigate to https://magento.test


#### 2. Install sample data

`bin/cli php bin/magento sampledata:deploy`

`bin/cli php bin/magento setup:upgrade`

#### 3. Disable 2FA - Google Auth

`bin/cli php bin/magento module:disable Magento_AdminAdobeImsTwoFactorAuth`

`bin/cli php bin/magento module:disable Magento_TwoFactorAuth`

#### 4. Access to dashboard
Navigate to https://magento.test/admin

`user: john.smith`

`pass: password123`

#### 5. Two factor authentication
1. Navigate to http://localhost:1080/
2. You should receive a link to scan the code
3. Complete the process using Google Auth

#### 6. Access to PHPMyAdmin

Navigate to http://localhost:8080

`user: magento`

`pass: magento`

#### 7. Set up domain name
`bin/magento setup:store-config:set --base-url="https://www.mydomain.com/"`