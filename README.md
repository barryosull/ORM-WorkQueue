ORM-WorkQueue
=============

A class that lets you queue work, made for FuelPHP, designed to work with any fuel ORM model.

About
=============
This is a workqueue, it allows you to queue work to be performed by a model, one that has the FuelORM interface. A task can be popped of the queue and run at any time. Tasks that throw exceptions are put back on the queue, if they fail 5 times in a row,they are deemed a failure and will not ne tried again.

It will work with any existing FuelPHP ORM model, or one that implements the interface included in the "interface" folder.

How to use
=============
Firstly, pop things into the correct folders.
Put the model into your "models" folder.
Put the migration into your "migrations" folder, be sure to replace the "XXX" with the right migration numbers and then run the migration.
If you want to test this thing, just pop the test into the "tests" folder and give it a whirl.
If you intend to use this with NonFuel Objects, get them to extend the included interface.

For the best idea of how it works and how it can be used, I'd advise reading the tests, they're pretty much living documentation for this class.


Future plans
=============
Certain values are hard coded, I'd like to make a config files instead.
