# AWS Serverless PHP SAM

This depends on the [stackery php lambda layer](https://github.com/stackery/php-lambda-layer) thanks to the maintainers. The aws php sdk is a customlayer created and published by me. 

This contains a serverless implementation which uses a couple of layers one for php 7.3 and the other for aws php sdk. This was just an exploration to know if I can make the php-mf work in the AWS Serverless deployment. This is just a guideline. and most of the handlers are empty. The reference application is not complete, as of now only the GET request to /Prod/info will return the php info output with some extended information. This shows that the php implementation works. The rest is just a POC and stored here as a future reference.