
![Collab (php)](http://raweden.se/public/github/collab-php-logo.png)

The Collab (php) is a simple and rubust remoting platform, written on top a modified version of [amfphp 2.0](https://github.com/silexlabs/amfphp-2.0)


### Supported (Client) Platforms
* ActionScript 3.0 using the `NetConnection` class, this also includes flex.
* AJAX and JavaScript using the `XMLHttpRequest`, a wrapper included in [download section](https://github.com/raweden/Collab.php).


### Bundled with perfect Utility Tool
**If your are addicted to eyecandy you will love this one.** Inspired by the Graph API explorer (facebook) i developed this little utility, it's written in JavaScript and utilizes the Json Gateway in amfphp.

### TODO
* Solve the issue with binary sending binary data in Json (plugin). In Amf we have the `ByteArray` type which is recognized as binary data in both ends, however there is no binary data-type in Json. The problem could be solved in diffrent manners, one approach could be to have a overrideMimeType() interface in the Json plugin, to allow commands to prevent the default serialization of the data and instead return the call as it's default mime-type, for example `image/png`. Another approach could be to  loop through the return object tree, to find and serialize the `ByteArray` object in a custom way, lets say with `base64`.


* Solve exception handling in Json (plugin). The default implemented way to handle exception where just poor, in the beta branch there are serialized into object (feels more useful). However there is no good way to determine whether the retrived data is a `result` or `fault`, where fault is when a exception is thrown by the command. Alternative solution is to wrap the resulting data object into another and provide additional meta-data about the call: `{"data":{..},"isFault":false}`.
	

* Solve issue cross-platform serializing data types. As mentioned in a issue above there is huge a problem sending binary data in Json, and a better interface is needed to determine at method level which type that is most useful to return. For instance, it's better to return a `ByteArray` for representing binary data than a string when handling a Amf request. However when the same command handles a json request, the `ByteArray` type is quite useless and will contain alot of escaped characters that won't be recognized and resolved at client-side.

* Provide utilities that exposes the type of request `Content-Type`.

* Refactor `GatewayConfig`, `PluginManager`to be controlled form a configuration file, to allow remote enable and disable plugins etc.

* Provide a implementation that makes services aware of the format that the data will be serialized in, services that return a `ByteArray` to flash may better return their values as string when serialized into JSON.

* Remove Native scrollers in the Tool and replace them with a overlay scroller, however this should only be done with desktop clients.

* Fix issues with the Tool running in mobile browser, performance is bad.


### Done 
* Refactoring the naming convention used by the **silexlabs**, in favor of classnames without the `Amfphp_Package` prefix.
* Fixed issue with Amf3.
* Developed a better service browser and removed the default service browser.

*  *  *

Copyright © 2011 [Raweden](http://raweden.se)

### Term of Use
**You are free:** to copy, distribute, display, and perform the work
to make derivative works
to make commercial use of the work
**Under the following conditions:**

* **Attribution** — You must give the original author credit.
* **Share Alike** — If you alter, transform, or build upon this work, you may distribute the resulting work only under a licence identical to this one.

![CC Licence](http://raweden.se/public/github/by.png)  ![CC Licence](http://raweden.se/public/github/sa.png)


