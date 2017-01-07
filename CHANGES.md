#Change Log

##Codepath Analyzer
Added the commands: apps codepath analyze [URI]. This function shows you what
apps will respond to a given uri. This helps solve the problem of an app that
appears to not be responding by confirmgin whether or not the regex for the
uri properly applies to that uri.

##Visually trace code execution on your application.
Added set visualtrace [on|off]. This function puts tags throughout the page
at the physical location ont he page where the app fires so you can trace the
actions through their execution. You can see what executes, in what order, and
you can even use it to debug whether or not a given app or action is firing
properly (or at all) an din the correct place. It is especially useful in
debugging elements that are repeating on the page, whcih should not be.

##App and action run limits.
Added run limits to apps and actions. Apps can now be configured to run once
(as a whole) or, individual actions can be limited to run only X number of
times. To do that, you simplyt configure the app.json file with the name of the
action and the number of times it is allowed to run. This is especially useful
when you have multiple apps taht respond to the same action, but that should
only show content one time. For example, the action include-navigation may have
multiple apps that respond to it in order to re-use the code. However, you don't
need to show the navigation more than once per page. So, we can limit the
number of times that app runs that action. In this particular example, the
'first one wins' there,fore it is important to use the app / action priority
system to set teh app that you want to do the execution as a lower priority.
Otherwise, it will fire in teh order in which the apps are disocvered on disk,
which is not necessarily alphabetical, but is close.

An example of an app.json file, which limits the number of executions of a
given action is shown below:

###Example
    {
        "actionRunLimit": {
            "include-admin-navigation": 1
        }
    }
