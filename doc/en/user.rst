.. _user_guide:

##########
User guide
##########

Overview
--------

Centreon module Dynamic Services Management (DSM) is an extension to manage
alarms with an event logs system. With DSM, Centreon can receive events such as
SNMP traps resulting from the detection of a problem and assign events
dynamically to a slot defined in Centreon, like a tray of events.

A resource has a predefined number of slots (services) on which alerts will
be assigned to (stored). While a certain event has not been actively
acknowledged by a human action or cleared by another trap with an **OK** status,
it will remain visible in the Centreon web-based GUI. Once the event is
acknowledged or cleared, the slot becomes available for new events.

The goal of this module is to replace the basic trap management system of
Centreon. The basic trap management system runs with a single service and alarms
overwritten by successive alarms.


Architecture
------------

The event must be transmitted to the server via an SNMP trap. The SNMP trap is
thus collected by the **snmptrapd** daemon. If the received parameters are valid
(authorized community), then it sends snmptrapd trap SNMP binary SNMPTT.
Otherwise, the event is deleted.

Once the SNMP trap has been received, it is sent to the **centreontrapdforward**
script which writes the information received in a buffer folder
(by default: /var/spool/centreontrapd/).

The **centreontrapd** service reads the information received in the buffer
folder and interprets the traps received checking, in the centreon database, the
actions necessary to process these events. In Centreon DSM, we execute a
**Special Command**.

This special command is executing the script **dsmclient.pl** with a few
arguments. This client will store the new trap in a slot queue that the daemon
reads every 5 seconds.

The daemon **dsmd.pl** will search in the database **centreon** for slots (slot
pools) associated with the host. If no slot pool is found, the event is deleted.
Otherwise, the script will search for a free slot in the pool. If at least one
slot is free, then it will transmit external commands to the monitoring engine
to change the status of the slot. Otherwise the data will be kept in the
database and an alarm will be raised only after the release of a slot. A slot
can be released either by auto-acknowledgement or by a manual acknowledgement
from an operator.


Host configuration
------------------

Centreon DSM is different from the basic trap management system in the sense
that you can't link the passive service of the slot to the trap that you want
to monitor.
With Centreon DSM, you'll have to link the SNMP trap with any active service
that is currently **ENABLED** on a host. If the monitored host has at least one
active service, you can proceed to the next step_. Otherwise, you should create
a new host and select the host **Template** called
**generic-active-host-custom**. This template provides the bare minimum required
(the **Ping** active service) for the deployment of Centreon DSM. Make sure you
configure the **IP Address** and **SNMP Community and Version** parameters
correctly for your host.
Later_ in this document, we'll go through the steps required to link the host
active service with the SNMP trap.

.. _step:

Creating a passive service template
-----------------------------------

Centreon DSM slots are actually created out of "regular" passive services. In
order to facilitate the configuration of these passive services, it is more
convenient to create a passive service template. This passive service template
will be used by the Centreon DSM to create the multiple services (slots) for the
SNMP traps that you want to monitor.
In order to create the passive service template, you have to:

#. Go to the menu **Configuration > Services > Templates**
#. Click on **Add**

The table below summarizes all the required attributes of a passive service
template for Centreon DSM:

+--------------------------+---------------------------------------------------+
| Attribute                | Description                                       |
+==========================+===================================================+
| **Service Configuration** Tab                                                |
+--------------------------+---------------------------------------------------+
| Alias                    | Passive service template used with Centreon       |
|                          | DSM and SNMP traps                                |
+--------------------------+---------------------------------------------------+
| Service                  | generic-passive-service-dsm-custom                |
| Template                 |                                                   |
| Name                     |                                                   |
+--------------------------+---------------------------------------------------+
| Template                 | generic-passive-service                           |
+--------------------------+---------------------------------------------------+
| Custom Macros            | * **DUMMYSTATUS**: OK                             |
|                          | * **DUMMYOUTPUT**: Service is OK                  |
|                          | * **ALARM_ID**: empty                             |
+--------------------------+---------------------------------------------------+

.. warning::
   The custom macro **ALARM_ID** and its associated value **empty** are
   mandatory with Centreon DSM. They must be created together with the new
   service template.

An example of passive service template is available below:

.. image:: /_static/use/form-passive-service.png
   :align: center


Configuring the slot pool
-------------------------

In the Centreon web-based GUI, go to
**Administration > Extensions > (Dynamic Services) Configure** and click on the
**Add** button. In order to create or modify a slot pool, read the table below
for better comprehension of the role of each parameter.

+--------------------+---------------------------------------------------------+
| Parameter          | Description                                             |
+====================+=========================================================+
| Name               | This is the name of the slot pool.                      |
+--------------------+---------------------------------------------------------+
| Description        | This is a description for the slot pool.                |
+--------------------+---------------------------------------------------------+
| Host Name          | The name of the host linked to the slot                 |
|                    | pool.                                                   |
+--------------------+---------------------------------------------------------+
| Service            | The base service template used to create the            |
| template           | service slots on the host. This template must           |
| based              | be a passive service template **only** and a            |
|                    | custom macro needs to be created on it. The             |
|                    | custom macro **must** be named **ALARM_ID**             |
|                    | and its default value must be set to                    |
|                    | **empty**.                                              |
+--------------------+---------------------------------------------------------+
| Number of slots    | The number of slots (services) that Centreon            |
|                    | will create on the selected host upon                   |
|                    | validation of this configuration form.                  |
+--------------------+---------------------------------------------------------+
| Slot name prefix   | This prefix is used to name the services that           |
|                    | represent each slot. The name of the service            |
|                    | will be the **Slot name prefix** followed               |
|                    | by a four-digit number, starting from 0001 and          |
|                    | and all the way up to the total                         |
|                    | **Number of slots**.                                    |
+--------------------+---------------------------------------------------------+
| Check command      | This **Check command** is used when the                 |
|                    | the service has to be forced into a free slot.          |
|                    | The check command must return an **OK** code.           |
+--------------------+---------------------------------------------------------+
| Status             | The status of the slot pool.                            |
+--------------------+---------------------------------------------------------+

In the following picture, you have an example of configuration.

.. image:: /_static/use/form-slot.png
   :align: center


When you save the configuration, Centreon will create or update all slots. If
you haven't changed anything else, you are all set. Otherwise, you
have to go to **Configuration > Pollers > Pollers** in order to generate the
configuration of the poller impacted by the changes. If you don't do that, you
will not see the results of your changes in the Centreon web-based GUI.

.. image:: /_static/use/conf-test.png
   :align: center

Once the configuration has been generated and validated on the web-based GUI,
you can then push the configuration files and restart the Centreon Engine.

.. image:: /_static/use/conf-restart.png
   :align: center


Configuring the traps
---------------------

The last step is to configure the traps that you want to redirect to the
Centreon DSM slots.
Nowadays, this configuration is slightly complex. Our intention is to simplify
it in future releases of the Centreon DSM.

To edit an SNMP trap that you want to redirect to the slot systems, go to
**Configuration > SNMP traps > SNMP traps** and you will find the following
configuration form:

.. image:: /_static/use/trap-form.png
   :align: center

In order to redirect alarms to slots, you have to tick the checkbox
**Execute special command** and call the **dsmclient.pl** using the
**Special Command** field in a similar way to the example below:

::

  /usr/share/centreon/bin/dsmclient.pl --Host @HOSTADDRESS@ --output 'Example output: $*' --id 'linkdown' --status 2 --time @TIME@

This command accepts a number of different parameters. In the following table,
you can find a description for each of them:

+---------------------+--------------------------------------------------------+
| Parameter           | Description                                            |
+=====================+========================================================+
| **-H \| --Host**    | Host address (IP address or Name) to which you         |
|                     | want to redirect the alarm. You can use the            |
|                     | variable **@HOSTADDRESS@** in order to keep            |
|                     | the same IP address received in the trap or            |
|                     | the can change it to any other IP address if           |
|                     | you intend to centralize all alarms on a               |
|                     | single virtual host, for example.                      |
+---------------------+--------------------------------------------------------+
| **--host-id**       | This is the Centreon internally-defined Host           |
|                     | ID of the managed device.                              |
|                     | The Host ID is used to assign a trap to a              |
|                     | certain host-slot pool pair whenever the same          |
|                     | trap can be generated by different hosts that          |
|                     | share the same IP address. It doesn't matter           |
|                     | if you're treating the same trap OID through           |
|                     | both DSM and the basic SNMP trap management            |
|                     | system at the same time. If you have more than         |
|                     | one host using the same IP address, use the            |
|                     | parameter **Host ID** instead of the parameter         |
|                     | **Host** to identify the host.                         |
|                     | In order to discover the Host ID, you can go           |
|                     | to **Configuration > Hosts > Hosts** and click         |
|                     | on the name of the host. The Host ID can be            |
|                     | obtained from the URL shown on the web browser         |
|                     | (the Host ID is the number that comes right            |
|                     | after **host_id=**).                                   |
+---------------------+--------------------------------------------------------+
| **--pool-prefix**   | This option should be set exactly like the             |
|                     | **Slot name prefix** that was previously set           |
|                     | up in the step `Configuring the slot pool`_.           |
|                     | The **--pool-prefix** option is mandatory              |
|                     | whenever you have at the same time:                    |
|                     |                                                        |
|                     | * Multiple DSM slot pools linked to any one            |
|                     |   single host and;                                     |
|                     | * Distinct traps assigned to different (but            |
|                     |   specific) DSM slot pools.                            |
|                     |                                                        |
|                     | If not set, all traps the will be directed to          |
|                     | the first slot pool created on Centreon (that          |
|                     | is linked to the host).                                |
+---------------------+--------------------------------------------------------+
| **-i \| --id**      | This is the ID of the alarm. The alarm ID can          |
|                     | be built with the concatenation of strings and         |
|                     | variables like "$1-$4". If you want Centreon           |
|                     | to perform auto-acknowledgement (a certain             |
|                     | trap 'resetting' the status of a slot to               |
|                     | **OK**), make sure the alarm ID is exactly the         |
|                     | same on the configuration of both traps.               |
|                     | Example: if the trap linkUp should reset an            |
|                     | alarm for the trap linkDown, you have to set           |
|                     | the same ID for these two traps. You could use         |
|                     | something like **--id 'Link Status'** for              |
|                     | both. If the ID is not the same, then Centreon         |
|                     | DSM will assign new alarms to different slots          |
|                     | (instead of resetting an alarm previously in           |
|                     | **WARNING** or **CRITICAL** status).                   |
+---------------------+--------------------------------------------------------+
| **-o \| --output**  | This is the output message that DSM will               |
|                     | provide to the correct slot when the command           |
|                     | is called. This output can be built with the           |
|                     | concatenation of strings and all $* values.            |
+---------------------+--------------------------------------------------------+
| **-s \| --status**  | This is the status (0 = **OK**,                        |
|                     | 1 = **WARNING**, 2 = **CRITICAL** or                   |
|                     | 3 = **UNKNOWN**) that you want the                     |
|                     | **dsmclient.pl** script to pass as parameter           |
|                     | to the alarm. You can use **@STATUS@** in              |
|                     | order to inherit the status resulting from the         |
|                     | advanced matching rules system.                        |
+---------------------+--------------------------------------------------------+
| **-t \| --time**    | This is the time that you want to pass to              |
|                     | DSM. In order to keep the actual trap                  |
|                     | reception time, you can use **@TIME@**.                |
+---------------------+--------------------------------------------------------+
| **-m \| --macro**   | This is a list of macros and the values that           |
|                     | you want to update them to during the                  |
|                     | treatment of the alarm. The following syntax           |
|                     | must be used:                                          |
|                     | macro1=value1\|macro2=value2\|macro3=value3            |
|                     | This option is used when you need to update            |
|                     | macros at runtime on the Centreon Engine               |
|                     | without requiring a restart.                           |
+---------------------+--------------------------------------------------------+


Your configuration should look like this:

.. image:: /_static/use/trap-form-2.png
   :align: center

After saving the configuration, you'll have to generate the SNMP traps
configuration file. Go to **Configuration > SNMP Traps > Generate**, select the
poller, and tick the checkboxes **Generate trap database** and
**Apply configurations**. Finally, opt for **Restart** in the **Send signal**
drop-down list and click the button **Generate**.

You can now start the daemon on your server:

::

  systemctl start dsmd

You should now have DSM activated for all traps you have configured.

.. _Later:

Configuring the link between the trap and the host active service
-----------------------------------------------------------------

Earlier on `Host configuration`_, we mentioned that Centreon DSM differs from
the basic trap management system in the sense that you can't link the passive
service of the slot to the trap that you want to monitor. You have to link the
the SNMP trap with any active service that is currently **ENABLED** on a host.
Here is how you do this.

#. Go to the menu **Configuration > SNMP traps > SNMP traps**
#. Search and click the trap that you want to edit
#. Click the tab **Relations**
#. Use the **Linked services** textbox to search for any active service
   (e.g. **Ping**) on the host that generates that specific trap. Once you've
   found it, click to select it.
#. Click **Save**
