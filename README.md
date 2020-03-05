# Centreon DSM

Centreon Dynamic Services Management (DSM) is an extension to manage alarms with
in a similar fashion to an event logs system.


## Installation

    yum install centreon-dsm-client centreon-dsm-server


## DSM Client Usage

 - Usage after release 2.0.

		/usr/share/centreon/bin/dsmclient.pl {--help|{--Host <hostname>|--host-id <host-id>} [--pool-prefix <pool-prefix>] [--id <id>] [--time <timerequest>] --status <status> [--output <output>]}
			--help               Help
			-H|--Host            The hostname or IP address of the host linked to trap services
			--host-id            The ID that represents a certain host in the database
			--pool-prefix        The pool-prefix of a slot pool linked to a host
			-i|--id              ID (a string built to match a specific alarm)
			-o|--output          The output message to display in Service Comment
			-s|--status          The statuses recognized by the engine are 0 = OK; 1 = WARNING; 2 = CRITICAL; 3 = UNKNOWN
			-t|--time            The time epoch for the trap request
			-m|--macro           List of macros and their values that need to be updated


 - Usage before revision 34: deprecated.

		./dsmclient.pl hostname timerequest output
			hostname            The hostname with trap services
			timerequest         The time epoch for the trap request
			output              The output to display in service comment

In this version, the status was always CRITICAL.
