# MultiSpamDelete

A PHP function designed to be run as CRON job on a mail server.

Takes any identically titled email messages that synchronously arrived to a particular folder in multiples and moves them to a folder of your choice, then compiles a list of the messages that were removed.

Our email server gets hundreds of spam messages a day, and the traditional spam filters such as SpamAssassin either miss a significant portion of them or overzealously mark useful mail as spam. Because multiple usernames on the server funnel into the same inbox, spam tends to arrive in duplicates or triplicates, making it even more annoying to sort through. Since regular messages are almost never sent to multiple addresses at once, I wrote this script to thin out the barrage of spam that I have to sort through by targetting only those that are sent shotgun style to all usernames on the server.