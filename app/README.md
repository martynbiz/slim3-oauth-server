# JapanTravel API #

This is the JapanTravel secure API to allow other applications to access JapanTravel
applications' data.

For example:

GET /jt/articles
POST /jt/articles
POST /qa/questions

Possible uses:

* After posting a question to QA, query JT for related articles
* When viewing questions, list relevant articles
* Tanemura's app
* My android app

oauth1 vs oauth2: oauth2 over https is more familiar


Build into ACQ or New app?

ACQ
+ access to existing libraries
- would essentially need to build a new module from scratch
- lots of new tables required
- crap urls: oauth.jt.com/articles, www.jt.com/questions/questions

New app (slim)
+ nice to work in, modern, use non-zend libraries
+ testing
+ can make a nice home project
+ it's own db
- new connections to acq db, etc. requires multiple connections
+ basis for new jt3? new models etc

If multiple apps:
+ centralize all services (qa, reputations, jt, etc)
+ better urls: oauth.jt.com/jt/articles, oauth.jt.com/qa/questions
