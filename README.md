[[https://s3.eu-west-2.amazonaws.com/portfolio-resources/Github/PHPCast.png|alt=PHPCast]]

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
5. MPlayer *
6. SCDL (Python Library) *

*Named sections explain on how to install.

#MPlayer
MPlayer is the audio controller for playing files in the background. The PHP interacts with MPlayer via the command line calling the UNIX commands on the application. 

MPlayer has a named pipe mode which allows us to create a "fake file" that allows PHP or any other application to write to the file which the application will read and execute.

Example
This command will pause the playback from the background command which is listening for commands via the named pipe.

	echo "pause" > /tmp/namedpipe


#MPlayer (Installation)
if you are running a debian / ubuntu system you can use the aptitude package manager to install MPlayer.

	sudo apt install mplayer

Other UNIX systems will allow downloading of MPlayer using their package managers.

If you're UNIX system does not support MPlayer via the package manager or you are on a OSX installation then download the binaries from here : [MPlayer Download](http://www.mplayerhq.hu/design7/dload.html) 

#SCDL (SoundCloud Downloader)
SCDL downloads songs from SoundCloud. PHPCast uses this Python library to download the songs to the /Stream directory on the server.

History of SCDL
SCDL has been around for a while now it used to be a bash script which interacted with the SoundCloud API. The developer decided to change from bash to Python.

#SCDL (Installation)
As SCDL is a Python library it will be able to run on any operating system that allows Python to be installed.

To install SCDL click the download link below then download the ZIP 
[Download SCDL](https://github.com/flyingrub/scdl)

Example 

	scdl -l www.soundcloud.com/user/song-name 

SCDL downloads  the file from SoundCloud and puts the file in the directory from where the command was called.
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
8. Create a MPlayer Class which acts as a wrapper for MPlayer on UNIX systems. (Active)
