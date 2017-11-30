# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|

  config.vm.box = "ubuntu/xenial64"
  config.vm.hostname = "prider.local"
  config.vm.provision :shell, path: "vagrant/provision.sh"
  config.vm.synced_folder ".", "/vagrant", nfs: true

  config.vm.network "private_network", ip: "192.168.58.64"

  config.hostsupdater.aliases = ["prider.local"]

  config.vm.provider :virtualbox do |vb|
    vb.customize ["modifyvm", :id, "--memory", "4096"]
    vb.customize ["modifyvm", :id, "--cpus", 2]
  end

end
