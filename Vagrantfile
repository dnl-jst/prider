# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|

  config.vm.box = "ubuntu/trusty64"
  config.vm.hostname = "prider.local"
  config.vm.provision :shell, path: "vagrant/provision.sh"
  config.vm.synced_folder ".", "/vagrant", {:owner => "www-data", :group => "www-data"}

  config.vm.network "private_network", ip: "192.168.58.64"
  # config.vm.network "public_network"

  config.hostsupdater.aliases = ["prider.local"]

  config.vm.provider :virtualbox do |vb|
    vb.customize ["modifyvm", :id, "--memory", "2048"]
    vb.customize ["modifyvm", :id, "--cpus", 2]
  end

end
