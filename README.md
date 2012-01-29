#Collab (php)
The Collab (php) is a simple remoteing library written in php, collar are similar to it's node equal but are simplified and don't support alive connection between the server and client. Collab is based on [amfphp 2.0](https://github.com/silexlabs/amfphp-2.0).

### Supported Platforms
* ActionScript 3.0 using the `NetConnection` class, this also includes flex.
* AJAX and JavaScript using the `XMLHttpRequest`
* XML-RPC (supported in a wide range of languages).

**amfphp 2.0** is free and open source software, and an essential brick for the development of Web Applications. amfphp is used in projects that cover a wide spectrum, from games to business applications. The role of amfphp is to make connecting an application running in the browser with a server in the cloud as simple as possible. Applications no longer run only on desktops, but must also be available on a variety of smartphones and tablets. It is becoming increasingly complex to code with the diversity of technologies used in these terminals. amfphp is the best solution for creating accessible services to all terminals. Developers can focus on features unique to their projects, regardless of the communication between client and server.
How does it work?  
amfphp works as an entry point for your client. Your client sends a request to a PHP script on your server where amfphp is loaded. It parses the request, loads the requested service, calls it, and returns the answer accordingly. amfphp is maintained by Silex Labs and this is a fork by **raweden** to develop a uniform bundle to support **rpc** requests from a wide range of languages and platform.


### Issues to solve
* Provide a implementation that makes services aware of the format that the data will be serialized in, services that return a `ByteArray` to flash may better return their values as string when serialized into JSON.
* Provide a configuration file for plugins and the runtime setup itself.
* Token based authorization.
* Add **XML-RPC** plugin to support another wide range of client-side 

### Goals for this fork!
languages.
* Remove the naming convention used by the **silexlabs**, in favor of classname without the `Amfphp` and `package` prefixes.
* Posible add support for sub-directories both in the `Plugins` and `Service` folder, or support for multible plugin folders.
* Cleaned up the default Service browser, [click here to preview](http://raweden.se/public/wiki/ServiceBrowser.png). (Done but not synced yet).

### Related Links

[Documentation](http://silexlabs.org/amfphp/documentation/)  
[Forums](http://sourceforge.net/projects/amfphp/forums)  
[Source Code](https://github.com/silexlabs/amfphp-2.0)  
[Package Reference](http://community.silexlabs.org/amfphp/reference/)  
[Silex Labs](http://www.silexlabs.org/)

**Disclaimer** most of above documentation is written by [silexlabs](http://silexlabs.org/amfphp/) with a touch of **raweden**.

### License
**Copyright (c) 2009-2011, Silex Labs**
All rights reserved.  
[New BSD license](See http://en.wikipedia.org/wiki/Bsd_license)

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met: 
 
* Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.  
* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.  
* Neither the name of Silex Labs nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

**THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL SILEX LABS BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.**