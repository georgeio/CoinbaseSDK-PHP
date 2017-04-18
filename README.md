# CoinbaseSDK-PHP

CoinbaseCustomSDK - A custom class built to handle coinbase transactions.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. 

Simply download a copy of the class file CoinbaseCustomSDK.php and include it in your php script. 
instantiate an object of the class CoinbaseCustomSDK e.g : 
```php
$cb_handle = new  CoinbaseCustomSDK('API_KEY', 'SECRET_KEY');
```
You can now call public methods of the CoinbaseCustomSDK class on the initialized variable.

## Example


### Get Account ID
```php
$cb_handle->getAccountId();
```

### Get Coinbase Server Time
```php
$cb_handle->getServerTime();
```

### Get Accounts Associated To The API Key
```php
$cb_handle->getAccounts();
```

### Create New Address For Receiving Bitcoin/Ethereum Payment
```php
$name = "New Address for Payment";
$cb_handle->createNewAddress($name);
```

### Create New Address For Receiving Bitcoin/Ethereum Payment
```php
$address = "ENTER THE BITCOIN ADDRESS / ADDRESS ID";
$cb_handle->getAddressTransactions($address);
```

## Prerequisites

php >= 5.6,
CURL,
Coinbase API Key,
Coinbase Secret Key

## Built With

PHP

## Authors

George Imoedemhe - Initial work - PurpleBooth

## Contributing

Fork it!
Create your feature branch: git checkout -b my-new-feature
Commit your changes: git commit -am 'Add some feature'
Push to the branch: git push origin my-new-feature
Submit a pull request :D

## License

This project is licensed under the GNU Lesser General Public License - see the LICENSE.md file for details
