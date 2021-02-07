## Установка docker-compose на centOS 7

### Добавление нового пользователя с правами sudo

```bash
adduser fastuser
passwd fastuser
usermod -aG wheel fastuser
```

### Установка docker (от пользователя fastuser)

```bash
wget -qO- https://get.docker.com/ | sh
sudo usermod -aG docker $(whoami)
sudo systemctl enable docker.service
sudo systemctl start docker.service
```

### Установка docker-compose (от пользователя fastuser)

```bash
sudo yum install epel-release
sudo yum install -y python-pip
sudo pip install docker-compose
sudo yum upgrade python*
```

### Необходимые настройки
```bash
echo 'vm.max_map_count=262144' >> /etc/sysctl.conf
echo 'vm.overcommit_memory = 1' >> /etc/sysctl.conf
sysctl -p
```

## Clear All
```bash
docker kill $(docker ps -q) ; docker rm $(docker ps -a -q) ; docker rmi $(docker images -q -a)
```
