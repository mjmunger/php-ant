#Change Log

#This commit
##Added priorities to routed actions.

Routed actions now accept an optional priority argument. For backwards compatibility, the argument is optional, and any routed action declaration that does not have an explicit priority declaration will default to 50.

AppEngine::runRoutedActions now cycles through the apps in the following order:

1. It loops through all available apps to build a list (array) of apps that respond to the requested URI. It stores this in a three-dimensional array (tuple).

2. Next, it sorts the arrays based on the declared priority. Although all apps and actions all exist in this array, it's OK, because when we loop through them again with a unique list of actions, we'll skip over the irrelevant ones.

3. Now, we're ready to build that list of unique values, so we loop through the array and store copies of the actions that are being requested. This is obviously duplicated because some actions are responded to by more than one app. So...

4. We use array_unique() to create a unique list of actions.

5. Now, armed with a unique list of actions that we need to run, we can loop through the master array,and execute those actions ona  per action basis, in the correct priority order.

6. Results arrays ar aggragated normally.

See in-line code documentation for this method for more information including an example array.

#Previous Commits

#Commit 060a5b1bf0ca1d65cf6217fcbea5d1325f598102

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
