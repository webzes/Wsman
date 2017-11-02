# Wsman - A PHP package for WS-Management Protocol

-- Coming soon!

## Installation

You don't (just yet). The package is not quite ready ^_^

## Usage

### Connecting

```php
use c0py\Wsman;

$wsman = new Wsman($host = 'TARGET-PC', $username = 'test', password = 'security');
$session = $wsman->connect();
```

### Simple Queries

Get WinRM Config

```php
$config = $session->Get("winrm/config");
```

### WMI Queries

```php
$response = $session->Get('wmi/root/cimv2/Win32_OperatingSystem');
```

Or

```php
$query = "wmi/root/cimv2/*"
$filter = "SELECT * FROM Win32_NetworkAdapterConfiguration WHERE IPEnabled = true";
$dialect = "http://schemas.microsoft.com/wbem/wsman/1/WQL";

$response = $session->Enumerate($query, $filter, $dialect);
```
