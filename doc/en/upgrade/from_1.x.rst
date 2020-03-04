.. _install_from_packages:

==========================
From 1.x to 2.x (or newer)
==========================

*****************
From RPM packages
*****************

Centreon provides RPM packages for its products through the Centreon open source
version available free of charge in our repository.

These packages have been successfully tested in version 7.x CentOS and Red Hat
environments.

****************
Centreon upgrade
****************

Upgrade a central server
------------------------

These instructions apply when upgrading the Centreon DSM extension on a
central server. The Centreon DSM server and client packages must be installed on
the main server.

The version 1.x of Centreon DSM doesn't contain a server and a client: the
client embeds the intelligence and the server is just a cron task.

This organization was a problem due to a number of reasons. That's why this
module completely changed with this new major version.

To upgrade, run the following command:

::

  $ yum upgrade centreon-dsm-server centreon-dsm-client


After installing the RPM packages, you have to finish the module installation
via the web-based GUI. Go to **Administration > Modules**.

Install the Centreon DSM module.

.. image:: /_static/installation/module-setup.png
   :align: center

Your Centreon DSM Module is now installed.

.. image:: /_static/installation/module-setup-finished.png
   :align: center

In order to migrate the trap configuration, you have to change all the commands
configured in each trap.
For each command, replace the following path:

::

  /usr/share/centreon/bin/snmpTrapDyn.pl

by

::

  /usr/share/centreon/bin/dsmclient.pl

The other parameters remain the same.


Upgrade a poller
----------------

These instructions apply when upgrading the **Centreon DSM** extension on a
poller. Only the Centreon DSM client package must be installed on the poller.

To upgrade Centreon DSM, run the following commands:

::

  $ yum erase centreon-dsm
  $ yum install centreon-dsm-client

You now have to configure MariaDB in order to enable the poller to connect to
the central server databases **centreon** and **centreon_storage** using the
user **centreon**.

In order to do that, you have to connect to your MariaDB instance with the
**root** user and issue the following commands:

::

  $ GRANT SELECT ON `centreon`.`*` TO 'centreon'@'POLLER_IP';
  $ GRANT SELECT, INSERT, UPDATE ON `centreon_storage`.`*` TO 'centreon'@'POLLER_IP';

Now, from your poller, try to connect to the central server with the MySQL
client using the **centreon** user. If you have problems to configure the
connection to MariaDB, please refer to the database documentation:
https://mariadb.com/kb/en/grant/


Base configuration of pollers
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The communication between a poller and the central server uses the MySQL
client/server protocol. The DSM client needs to have access to the central MySQL
server in order to store new alarms.

.. note::
   The new trap system **centreontrapd** doesn't need access to the database but
   Centreon DSM does.
