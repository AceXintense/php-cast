# PHPCast
Laravel Application that allows the user to download and play songs on the server!

#What does PHPCast do?
1. PHPCast allows the user to submit URL 	requests to the server which will download a .mp3 version of the URL which will then be able to be played from the server.
2. PHPCast has a front-end that will allow you to manage the requested files via play, pause, shuffle. All files get added to a queue which can be cleared all of the file activity is tracked in MySQL.

#What is PHPCast's purpose?
PHPCast is using the idea of the Google Chromecast where you can stream videos / music to the device. This implementation can be  be used on any computer / server as long as all the prerequisites are installed and the device has a audio output. PHPCast has been built from the ground up using Laravel and PHP so that anyone can install this on there Linux devices.

#Requirements
1. PHP 5.6 > 7
2. PHP MySQL 
3. MySQL
4. Composer (setup.sh will install this)

#Setup
I will be adding a script called setup.sh to the project that will provision the server that it will be applied to. The script will install all of the prerequisites for the project to run.

