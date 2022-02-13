
# Selfdestructable Messages

This project lets the user generate messages, which will self-destruct after they are read.

New messages will be saved saved to /tmp/msg/ with a random hex number as filename. The user then recives a URL that should be sent to the receiver (Copy to clipboard / share to WhatsApp links included)

When accessed with an id, access to a corresponding file in /tmp/msg/ is tried.
The id can either be passed as request parameter "id" or as "SEO friendly URL" (In which case the webserver needs to be configured accordingly, example apache config will be added)

TODO:
* Encryption of messages
* Die of shame for writing in PHP :D 

DONE:
* Brute force protection (IP ?)


