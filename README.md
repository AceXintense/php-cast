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

#Things to do
1. Clean up the code base and change the structure of the code to flow better!
2. Make use of the /App/System/Utilities.php with implementing smaller functions.
3. Create the setup.sh (Important) this will enable users to quickly get up and running with it on there devices.
4. Expand upon the front-end to make the application look more appealing / more professional for people.
5. Create a google chrome plugin allowing to request from just a click on the page.
6. Add Youtube downloader to the backend or come up with a new solution using something like PhantomJS to render the page and record the audio.
7. Create a user account system which is presented on load of the software if the user is not logged in. (Deletes all songs on the Disk when you log out and redownloads all the songs on login.) (Now in development ) this should also be an optional feature.
