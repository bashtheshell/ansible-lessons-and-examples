# Ansible Wordpress

The playbook examples were mostly borrowed from Michael Heap's [Ansible: From Beginner to Pro](https://www.apress.com/us/book/9781484216606) book. Here you will find two variants that are contrived from the book. 

The main objective of this document is to provide a primer on Ansible with an immediate useful proof-of-concept for Wordpress lovers or those who are interested in building a LAMP stack. Although, we'll be using nginx instead of apache.

*ansible-wordpress-one-line* folder contains a playbook written in legacy YAML format that Heap used in his book. which is still fully functional with Ansible 2.7 at the time of this writing. The preferred style, [action shorthand](https://docs.ansible.com/ansible/2.7/user_guide/playbooks_intro.html#action-shorthand), is included in *ansible-wordpress-multi-line*. We'll cover both styles by starting with the legacy style first. Before we get into those examples, we need to cover what each playbook should at least contains.

## Most Basic Playbooks Start with...

### The following content:

```
---
- hosts: all
  become: true
  tasks:
```

While the triple dash `---` isn't required by Ansible, it'd help us quickly become aware that we're dealing with an YAML file.

The `- hosts:` line is always required in every playbook. This line also signifies a single play. The *all* [default group](https://docs.ansible.com/ansible/2.7/user_guide/intro_inventory.html#default-groups) suggests that every host in the inventory file &mdash; usually located at `/etc/ansible/hosts` &mdash; will be applied.

Using `become:` line is a highly-recommended security practice when managing hosts with Ansible. This works well when a passwordless sudo user is available on the remote hosts, and this assumes the `--user` option is being passed to the `ansible-playbook` command. Otherwise, you can use the `remote_user:` line to specify the sudo user on the remote machines.

Lastly, the `tasks:` line comprises list of tasks, which is executed in order one at a time, with each task running a module.

### A Simple Example:

```
---
- hosts: all
  become: true
  remote_user: vagrant
  vars:
    old_file: /tmp/some_old_file
    new_file: /tmp/new_file
  tasks:
    - name: Copy the old file to the new file
      copy: src={{ old_file }} dest={{ new_file }}
      notify: remove old file
  handlers:
    - name: remove old file
      file: path={{ old_file }} state=absent 
```
 
So far, we've covered `- hosts:`, `become:`, `remote_user:`, and `tasks:` above. `vars:` line is self-explanatory here, and `handlers:` will be discussed later.

In the above example, a single play is run on all remote machines listed in the inventory file. It's assumed there exists a remote sudo user, vagrant, on each machine. The play simply copy the old file, `/tmp/some_old_file`, and overwrite the file, `/tmp/new_file`. After a successful copy, the original file will be removed. Although, the play will fail if the source file doesn't exist.

As for the command, you'd run the command below from the controller host (machine that manages multiple remote servers). Please keep in mind that this is a demonstration. Later, we'll get to the actual snippets that you can immediately test.

`ansible-playbook -i '/etc/ansible/hosts' example-playbook.yml` 

## Quick Start Guide:

For a quick practical demonstration, you can use Vagrant and VirtualBox to run the playbook. You can also use a cloud provider of your choice if you prefer. In this section, we'll cover both Vagrant, coupled with VirtualBox, and cloud provider's Ubuntu VM. We'll be using Wordpress 4.9.8.

Once the playbook runs successfully, you can use the IP address shown in the playbook's output. It's printed in the 'debug' output right after the `TASK [Gathering Facts]` line. Paste the IP address in your web browser and you should see a Wordpress site titled 'Sample Site Title'. You can log in with the username, `wordpress_user`, and password, `Bananas&Bananas.`.

### Vagrant with VirtualBox:

This step assumes you have Vagrant, VirtualBox, and Ansible installed, and you're using most-recent version of Mac OSX, Linux, or Windows. No special configuration is needed after installing all three software. 

However, for Windows, you won't be able to directly use the scripts below. you'd need to download the repository and navigate to the appropriate directory. Additionally, you'd need to create a Vagrantfile and paste the configuration in it.

Once you have all software installed, you can paste the script directly on the command-line in a terminal:
```
#
# Download repo in home directory and go to subdirectory
#
cd ~
git clone https://github.com/bashtheshell/ansible-lessons-and-examples.git
cd ./ansible-lessons-and-examples/wordpress-playbook_michael-heap/ansible-wordpress-multi-line/

#
# Create the Vagrantfile
#
cat << 'EOF' > ./Vagrantfile
Vagrant.configure("2") do |config|
  #
  # Run Ubuntu Server 14.04 (64-bit) LTS (Trusty Tahr) VM guest
  #
  config.vm.box = "ubuntu/trusty64"

  # To use other Ubuntu Servers below, comment the above line and...
  #
  # Uncomment the next line for Ubuntu Server 16.04 LTS (Xenial Xerus)
  #config.vm.box = "ubuntu/xenial64"
  #
  # Uncomment the next line for Ubuntu Server 18.04 LTS (Bionic Beaver)
  #config.vm.box = "ubuntu/bionic64"

  #
  # Use bridged interface on VM
  #
  config.vm.network "public_network",
    use_dhcp_assigned_default_route: true

  #
  # Run Ansible from the Vagrant Host
  #
  config.vm.provision "ansible" do |ansible|
    ansible.playbook = "provisioning/playbook.yml"
  end
end
EOF

#
# Set up and run the VM (no provision)
#
# NOTE: This step may requires interactive input if you have more than one network interface. 
# Please respond to the prompt: "Which interface should the network bridge to?" 
#
vagrant up --no-provision

#
# Run the playbook
#
vagrant provision


```

### Cloud Provider (Ubuntu VMs):

This step assumes you're logged in remotely as a root user on a clean Ubuntu VM. This script works only for Ubuntu Server 14.04 LTS or higher. A bootstrapping script is included to prep the VM to run Ansible playbook on itself (controller host using itself as a remote server) as we won't be using a second VM. We'll run two scripts separately here.

Copy and paste to run the bootstrapping script:
```
# Load bootstrapping script
cat << 'EOF' > ./bootstrap.sh
#!/usr/bin/env bash

apt -y update
apt-get -y install python-pip git
apt-get -y autoremove
useradd -m -s /bin/bash vagrant
echo 'vagrant ALL = NOPASSWD: ALL' > /etc/sudoers.d/vagrant
chmod 440 /etc/sudoers.d/vagrant
echo 'export PATH=$HOME/.local/bin:$PATH' >> /home/vagrant/.profile
su --login -c 'ssh-keygen -t rsa -N "" -f ~/.ssh/id_rsa' vagrant
su --login -c 'cp ~/.ssh/id_rsa.pub ~/.ssh/authorized_keys' vagrant
su --login -c 'chmod 600 ~/.ssh/authorized_keys' vagrant
su --login -c 'pip install --upgrade --user pip' vagrant
su --login -c '~/.local/bin/pip install --user ansible' vagrant
echo "++++++++++++BOOTSTRAPPING COMPLETE!++++++++++++++"
EOF
chmod +x ./bootstrap.sh
./bootstrap.sh


```

Then copy and paste to switch user environment and run the playbook:
```
# Change to 'vagrant' user environment and run the playbook on
su - vagrant
git clone https://github.com/bashtheshell/ansible-lessons-and-examples.git
cd ./ansible-lessons-and-examples/wordpress-playbook_michael-heap/ansible-wordpress-multi-line/
ansible-playbook -i ansible_hosts provisioning/playbook.yml


```


## One Line - Legacy

As you can see, the disguishable difference between one-line and multi-line is largely intended for readability. The one-line style may makes it easier to read the tasks in short chunks with all related options spanning horizontally on the same line. Of course, this would require one to use horizontal scrollbar. This is undesirable when using tasks containing module with multiple options. For example, the *unarchive* module used in the Wordpress playbook has four options. Let's take a look below.

```
    - name: Unzip WordPress
      unarchive: src=/tmp/wordpress.zip dest=/tmp copy=no creates=/tmp/wordpress/wp-settings.php
```

The four options are 'src', 'dest', 'copy', and 'creates'. The line would be longer if longer pathnames are used instead. Although, this can be "fixed" using the `vars:` line and defining the variables with the long pathnames there. It'd be a waste to list meaningless variables there, especially for the particular task described above as there's no better place other than `/tmp` to unarchive to a temporary location on most systems.

## Multi Line - Modern

Needless to say, readability remains to be the top reason we should write our playbooks using this style, going forward. However, the conversions aren't completely straightforward as you'd need to be aware of a few modules that'd require another option when using the action-shorthand. The `command:` module requires us to supplement the `args:` option when extending the module's option.

Below is the action-shorthand (preferred) version:
```
    - name: Generate new root password
      command: openssl rand -hex 7
      args: 
        creates: /root/.my.cnf
      register: mysql_new_root_pass
```

`args:` isn't used in older style here:
```
    - name: Generate new root password
      command: openssl rand -hex 7 creates=/root/.my.cnf
      register: mysql_new_root_pass
```

## Overview of Ansible Playbook

A major difference between using Ansible and a well-sophisticated script is that Ansible alleviates the burden of the massive complexity that'd be added to the script. Like other configuration management (CM) solutions, Ansible is designed with idempotence in mind. This means if Ansible can detect that a service is already running, it'd not need to attempt running the service again and skip it entirely. The same holds true for skipping installation for applications that are already installed. 

As you can imagine, a traditional script written in Bash or Python would be largely rewritten in order to become functionally equivalent to what an Ansible playbook can achieve in fewer lines. A traditional script would have multiple if/else and try/except statements in an attempt to be indempotent. 

Reading through the playbook is fairly trivial as Ansible strives to make their solutions simple enough for systems implementers to quickly work with, possessing almost zero CM knowledge and limited proficiency in scripting and system admistration. For the most part of the playbook, you can figure out what's happening in each task.

### A Closer Look at 'ansible-wordpress-multi-line' Playbook

Not all tasks in the playbook will be covered as some of them are self-explanatory. Let's take a look at the task using the `apt:` module.

#### Loop and Variable Substitution:

```
    - name: Install PHP
      apt: 
        name: "{{ item }}" 
        state: present
      with_items:
        - php
        - php-fpm
        - php-mysql
        - php-xml
```

The first line, `- name: Install PHP`, is arbitrary as you can come up with any name you desire for each task. It's not required, but it'd be a good practice to follow. In case you aren't familar with the `apt` command on Debian-based distros, it's a package manager that'd allow us to download packages. As you can see on the third line, the two sub-items for `apt:` are `name:` and `state:`. The `name: "{{ item }}"` may look peculiar, but the double curly brackets are placeholder for the [lookup plug-in](https://docs.ansible.com/ansible/2.7/plugins/lookup.html#using-lookup-plugins), 'item'. Specifically, they're [Jinja2 template](https://docs.ansible.com/ansible/2.7/user_guide/playbooks_variables.html#using-variables-about-jinja2), a basic form of variable substitution. The second sub-item, `state:` would tell Ansible that we'd like to have the package installed. If the package's already installed, then the state is true as the package would be present. The opposite state, absent, would uninstall the package if already installed. Of course, nothing will happen if package doesn't exist. With the next line, the `with_items:` lookup, which works in tandem with the aftermentioned 'item' plugin, contains 4 packages that'd need to be installed. 

Basically, a loop is being performed here by iterating four times. However, this is no longer the case for quite some time as `apt:` and other similar modules have improved over time to natively support installing/uninstalling all packages in one transaction. It'd be performing the equivalent of `apt-get install php php-fpm php-mysql php-xml` command. In the past and even up to this day, Ansible was employing a workaround using [squash-actions](https://github.com/ansible/ansible/issues/24581#issuecomment-361339485) in order to convert the loop to one transaction as indicated in the [example Ansible configuration file](https://github.com/ansible/ansible/blob/stable-2.7/examples/ansible.cfg). Here's the snippet below:

```
# squash actions
# Ansible can optimise actions that call modules with list parameters
# when looping. Instead of calling the module once per with_ item, the
# module is called once with all items at once. Currently this only works
# under limited circumstances, and only with parameters named 'name'.
#squash_actions = apk,apt,dnf,homebrew,pacman,pkgng,yum,zypper
```

This work-around is no longer needed and will be discontinued in [2.11 release](https://docs.ansible.com/ansible/2.7/porting_guides/porting_guide_2.7.html#using-a-loop-on-a-package-module-via-squash-actions). For the sake of this tutorial, the `with_items:` loops are left unmodified in both playbooks to be consistent with Heap's book as the new approach is incompatible with the one-line style, which is another great reason to abandon this practice. 

Here is the most up-to-date _**preferred method**_:
```
    - name: Install PHP
      apt: 
        name:
          - php
          - php-fpm
          - php-mysql
          - php-xml
        state: present
```

#### Creating Variables and Conditional Statements:

There will be times where the outputs or results of one task are needed in order to conditionally branch out to another task just like the if/else statements in most programming languages. This is what the `register:` [keyword](https://docs.ansible.com/ansible/2.7/user_guide/playbooks_variables.html#registered-variables) is intended for. Here's a snippet:

```
    - name: Generate new root password
      command: openssl rand -hex 7
      args: 
        creates: /root/.my.cnf
      register: mysql_new_root_pass
```

If you run `openssl rand -hex 7` on the command-line, you'd get a 7-bit hexidecimal value as your output. The `register:` line stores the output in the new 'mysql_new_root_pass' variable that can be used in later tasks after this line but not before.

It's very important that we take notice of the `creates:` parameter associated with `command:` module as the task won't run if the `/root/.my.cnf` file already exist. Without the `creates:` line here, the entire playbook would no longer be idempotent, as the `/root/.my.cnf` file would always be overwritten and can create a problem later on when attempting to log in MySQL server as root user again. Idempotence simply means the playbook should not change anything on the second execution after the first successful run.

Here is an example of how the 'mysql_new_root_pass' variable is used:
```
    - name: Remove anonymous users
      mysql_user: 
        name: "" 
        state: absent
      when: mysql_new_root_pass.changed
```

As you can see, the `when:` line is a conditional test to determine whether the task should be executed based on the boolean value of `mysql_new_root_pass.changed`. You may wonder how the trailing '.changed' string came in the picture. `register:` is a Python dictionary of the entire single task output that you'd see when using the verbose option `-v`. Here's a snippet of the previous task's output using verbose option:

```
TASK [Generate new root password]
******************************************************************************************
changed: [localhost] => {"changed": true, "cmd": ["openssl", "rand", "-hex", "7"], "delta": "0:00:00.006221", "end": "2018-10-28 19:00:22.482181", "rc": 0, "start": "2018-10-28 19:00:22.475960", "stderr": "", "stderr_lines": [], "stdout": "0e8a11624fec98", "stdout_lines": ["0e8a11624fec98"]}
```

You can only use the `register:` keyword once for each task. Since Ansible is written in Python, you can use any key value as an object. In our example, we'll use `mysql_new_root_pass.stdout` value which is `0e8a11624fec98`. Since this is a string object, we can manipulate the object to extract a particular result we'd like to see. This is going to be a strange example, but for the sake of the demonstration, let's say we only prefer string that contains at least a 'z' character. In order to do that, we could append the `find()` [method](https://docs.python.org/3/library/stdtypes.html#str.find), and the final statement would be `mysql_new_root_pass.stdout.find('z')`. The value would either be '-1' (for lack of 'z' character) or the first position of the character in the string. We can conveniently use the value as a boolean. You can add the `debug:` lines to see the value when running the playbook with verbose option.

```
    - name: Generate new root password
      command: openssl rand -hex 7
      args: 
        creates: /root/.my.cnf
      register: mysql_new_root_pass
    - debug:
        msg: "The value of mysql_new_root_pass.stdout.find is {{ mysql_new_root_pass.stdout.find('z') }}."
```

#### Module Options:

There are a lot of modules with options or parameters in the playbook that we did not cover. This tutorial assumes you are familiar with setting up Wordpress manually using the command-line. You can follow several Wordpress tutorials scattered all over the web to get a good fundamental concept before automating your Wordpress installation. Most modules, with their associated parameters, in this playbook are quite intuitive to Wordpress builders. In this section, we're only going to cover one module, `unarchive:`, and we're not going to go very deep on this.

`unarchive:` [module](https://docs.ansible.com/ansible/2.7/modules/unarchive_module.html), as you guessed it, unpack an archived file. Let's take a look at the snippet from the playbook.

```
    - name: Unzip WordPress
      unarchive: 
        src: /tmp/wordpress.zip 
        dest: /tmp 
        copy: no 
        creates: /tmp/wordpress/wp-settings.php
```

Looks straightforward, right? So what does the `copy:` line actually do? It's unclear to the initiates that it would copy the archive file from the controller host to the destination folder on the remote servers before extracting there. As you can see, it's important to know what each module option will do. Some modules have options that are implicitly set by default that you may not desire. So it's important you review the documentations on unfamilar modules. You can refer to Ansible documentation either through the official website or directly on the command line. To do so, run `ansible-doc unarchive`. At the time of this writing, the current Ansible production version is 2.7, and the `copy:` option has been deprecated in favor of `remote_src:` option, and both options are mutually exclusive. 

#### Making Tasks Idempotent:

As mentioned earlier, idempotence is a critical core goal that should be carefully followed. Sometimes we may encounter a situation where there is no well-defined module available that'd allow us to get the expected results we want. In our playbook, we use a `command:` [module](https://docs.ansible.com/ansible/2.7/modules/command_module.html), which is one of few core [modules without idempotency](https://docs.ansible.com/ansible/2.7/modules/list_of_commands_modules.html) built-in. Here's the snippet below:

```
    - name: Does the database exist?
      command: mysql -u root wordpress -e "SELECT ID FROM wordpress.wp_users LIMIT 1;"
      register: db_exist
      ignore_errors: true
      changed_when: false
```

In our case, we check to see if the database exists by using a workaround on the command-line, running `mysql -u root wordpress -e "SELECT ID FROM wordpress.wp_users LIMIT 1;`. This particular command checks if there exists a Wordpress user with the current Wordpress install. Since we're using a SQL backup of a base Wordpress installation created using MySQL 5.5 for quick demonstration rather than having you complete the initialization setup, no user has been created at this point during the first playbook execution. As a result, the command will fail with a non-zero return code as seen in `echo $?` output on the command-line.

We already know what the `register:` keyword does, but two remaining error-handling keywords haven't been introduced: `ignore_errors:` and `changed_when:`. The `ignore_errors:` line will prevent the playbook from aborting the playbook execution if the task fails. Normally, the playbook would exit when it reaches a failed task. When set to 'true', the playbook can run the remaining tasks in the playbook. For the task above, we'd like the failed error to be ignored every time it's triggered. How is it idempotent? It's not this task alone that does the magic but the fact that we can resume the task is an important piece to idempotence.

`changed_when: false` is necessary for tasks using modules that'd always report the state has changed each time the playbook is run. During the first playbook execution, most tasks would reflect the change status to be true, which is expected. The immediate subsequent playbook runs should be mostly false. However, for the tasks with `command:` module, the change status will always be true as Ansible has no way of confirming the change due to the module circumventing using raw, lower-level approach by design. So, it's necesary to use a boolean here. You can also perform a boolean test here such that you'd expect a particular output.

To get a complete picture of the idempotency magic here, first, the variable 'db_exist' is created. Remember, it's a dictionary of every possible results that can be seen by the verbose `-v` option. In the playbook, you'd see some of the subsequent tasks with the line, `when: db_exist.rc > 0`. As you can see, the return code (rc) will always be greater than 0 during the first playbook run since there's no database yet. The `when:` line ensures that the task won't run unless the condition is true. In our case, we'd want to copy over the backup database file from the controller host to the remote server only if the database hasn't been created or restored yet. Additionally, we'd also like to import the backup database after copying it to the remote server and make some changes to the imported database. These should not occur on the subsequent playbook run as the return code will be zero, which indicates there exists a database, specifically a Wordpress user.

Here's a snippet of the task output during the first playbook execution:
 ```
 TASK [Does the database exist?]
******************************************************************************************
fatal: [localhost]: FAILED! => {"changed": false, "cmd": ["mysql", "-u", "root", "wordpress", "-e", "SELECT ID FROM wordpress.wp_users LIMIT 1;"], "delta": "0:00:00.005227", "end": "2018-11-11 01:05:07.558776", "msg": "non-zero return code", "rc": 1, "start": "2018-11-11 01:05:07.553549", "stderr": "ERROR 1146 (42S02) at line 1: Table 'wordpress.wp_users' doesn't exist", "stderr_lines": ["ERROR 1146 (42S02) at line 1: Table 'wordpress.wp_users' doesn't exist"], "stdout": "", "stdout_lines": []}
...ignoring
 ```
 
#### Handlers:

When making configuration changes to the services such as Apache, restarting or reloading the service would be required. Is it possible to restart service in a separate task immediately after making configuration changes? Yes, but this isn't the best approach. The `handlers:` block, typically at the end of the playbook, exists for this purpose. The `notify:` lines in some tasks contain arbitrary labels, which work in conjunction with the names found in the `handlers:` block. The syntax for `handlers:` is quite the same as the ones used in the `tasks:` block. You can use most modules in the `handlers:` block.

The sole purpose of `handlers:` is to execute the tasks, defined in its block, only if the `notify:` lines in the `tasks:` block trigger the handler to run. Those `notify:` lines, in their respective tasks, should report the 'changed' status to be true in order to run the handler. Let's take a look at the task from the playbook below.

```
    - name: Create nginx config
      template: 
        src: templates/nginx/default 
        dest: /etc/nginx/sites-available/default
      notify: restart nginx
```

During the first playbook execution, the task's 'changed' status would be true. The `notify:` line will trigger the handler to restart the nginx server after all the tasks in the playbook have ran. The subsequent playbook run would not trigger the handler as the 'changed' status would be false. `changed_when:` keyword also comes in handy when you want to write task that'd only triggers the handler at a certain condition.

## What's Next...

The proof-of-concept demonstrated in this tutorial was just scratching the surface, and there are still a lot you can learn about Ansible. At this point, you can probably understand several introductory topics you'd find in Ansible documentations online.

The next step is to learn how to write better playbooks, and we have not covered *role*, which is *tasks* grouped together. In the future, I hope to expand on this tutorial and develop playbooks using roles instead then make comparisons with the original playbooks here. 

