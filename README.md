# magento2-payment-guard

    composer require vbdev/magento2-payment-guard

## Main Functionalities
- The module offers the possibility to limit transactions and transaction attempts within a time range.
- This is very useful to avoid malicious attacks with robots at checkout and also to avoid a possible overload of attempted card purchases, for example, at your external service.
- The module has a configuration area in the admin in **System->Payment Guard->Settings**:

![payment guard config](https://i.imgur.com/BUoTNrH.png)

- In this area we can enable the module and define our transaction limit and its time interval, as well as transaction attempts:

![payment guard settings](https://i.imgur.com/aQrFB78.png)

- Example: You do not have a very suitable security system for checkout due to your business model, which is why attacks were carried out with robots carrying out thousands of transactions in a short period of time, this caused your website to crash and entire spreadsheet of orders becomes disorganized, to avoid this you can use this module, defining a limit of transactions, such as 10, for example, in a time interval of 5 minutes (we agree that it is very unlikely for a normal person to carry out 10 transactions in a interval of 5 minutes), if it exceeds this limit in this time interval, the module will block further purchases for that IP.
- The blocks and all information of users who fall for this fraud will be listed in an admin grid in **System->Payment Guard->Payment Guard Logs**:

![payment guard admin grid](https://i.imgur.com/RzwRYzH.png)

- The module also offers the possibility for you to add an IP from the grid to the blacklist, thus preventing that IP from being able to make purchases in the store:

![payment guard blacklist](https://i.imgur.com/NjBdQaG.png)
![payment guard blacklist1](https://i.imgur.com/vGuZFKd.png)

- The same settings and behavior are replicated for the attempts.

## Install
### Type 1: Zip file

- Unzip the zip file in `app/code/Vbdev`
- Enable the module by running `bin/magento module:enable Vbdev_PaymentGuard`
- Apply database updates by running `bin/magento setup:upgrade`
- Flush the cache by running `bin/magento cache:flush`

### Type 2: Composer

- Install the module composer by running `composer require vbdev/magento2-payment-guard`
- enable the module by running `bin/magento module:enable Vbdev_PaymentGuard`
- apply database updates by running `bin/magento setup:upgrade`
- Flush the cache by running `bin/magento cache:flush`
