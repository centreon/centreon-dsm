############
Installation
############

===================
On a central server
===================

These instructions apply when installing the **Centreon DSM** extension on a
central server. The Centreon DSM server and client packages must be installed on
the main server.

Install the packages running the command::

    # yum install centreon-dsm

After installing the packages, you have to finish the installation of the module
through the web-based GUI. Go to **Administration > Extensions > Manager** menu
and search **dsm**:

.. image:: /_static/installation/module-setup.png
   :align: center

Your Centreon DSM module is now installed.

.. image:: /_static/installation/module-setup-finished.png
   :align: center

===========
On a poller
===========

These instructions apply when installing the **Centreon DSM** extension on a
poller. Only the Centreon DSM client package must be installed on the poller.

Install the package running the command:

::

    # yum install centreon-dsm-client

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
