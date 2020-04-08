# cli helper

## How to
  
Copy the phpmf.php to any convinenet folder, my preference is '~/bin/phpmf.php', and then add an alias to the .bash_aliases ( create if not exists ), as follows

```
alias phpmf='~/bin/phpmf.php'
```

### To start a new project

```
~/Projects/new-app$ phpmf init "The project description"
```
This will download the required files and create a boiler plate project.

### To create a new addon template

```
~/Projects/new-app$ phpmf new addon nameOfAddon
```
Will create a new addon function and file, invoking the same in main application code is the responsibility of the coder. The body of the addon also will need to be coded as the logic is only known to the coder.

### To create a new plugin template

```
~/Projects/new-app$ phpmf new plugin nameOfPlugin
```
Will create a class temple in the plugins folder. 

### To create a new view template

```
~/Projects/new-app$ phpmf new view nameOfView
```
Will create a view file in the views/ folder

