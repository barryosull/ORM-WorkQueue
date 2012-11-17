ORM-WorkQueue
=============

A class that lets you queue work, made for FuelPHP, designed to work with any fuel ORM model.

About
=============
This is a workqueue, it allows you to queue work to be performed by a model, one that has the FuelORM interface. A task can be popped of the queue and run at any time. Tasks that throw exceptions are put back on the queue, if the fail 5 times in a row, then they are deemed a failure.

It will work with any existing FuelPHP, ORM model, or one that implements the interface included in the interfaces folder.

How to use
=============
Put the model into your models folder, put the migration into your migrations folder, be sure to replace the "XXX" with the right migration numbers. If you want to test this things, just pop the test into the tests folder and give it a whirl.

For the best idea of how it works and how it can be used, I'd advise reading the tests, they're pretty much living documentation for this class.


Future plans
=============
For my purposes, the values, such as retry-rate and such are hard coded, in future I'd like to make a config file.
