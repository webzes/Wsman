# Wsman - A PHP package for WS-Management Protocol

-- Coming soon!

## Installation

You don't (just yet). The package is not quite ready ^_^

## Usage

### Create a client

```php
use c0py\Wsman\Wsman;

$client = new Wsman([
        'location' => "http://TARGET-HOST:5985/wsman",
        'login' => 'username',
        'password' => 'passowrd',
    ]);
```

### Simple Queries

Identity target

```php
$response = $client->identify();
```

Get WinRM Config

```php
$config = $client->get('winrm/config');
```

### WMI Query

```php
$response = $client->get('wmicimv2/Win32_logicaldisk', ['DeviceId' => 'C:']);
```

### Windows Registy Query

```php
$params = [
  'hDefKey' => '2147483650',
  'sSubKeyName' => 'SOFTWARE\Microsoft\Windows NT\CurrentVersion',
  'sValueName' => 'ProductName'
];

$response = $client->invoke('GetStringValue', 'wmi/root/default/StdRegProv', $params);
```
