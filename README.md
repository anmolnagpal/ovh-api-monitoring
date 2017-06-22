# OVH-Monitoring

OVH VPS/Cloud Monitoring via [OVH API](https://api.ovh.com/) using PHP.

![Screenshot](https://raw.githubusercontent.com/jbelien/OVH-Monitoring/master/screenshot.png)

## Configuration

### First step

Create credentials : <https://api.ovh.com/createToken/index.cgi?GET=/vps*&GET=/cloud*&GET=/status*>

### Second step

Create `monitoring.ini` file :

```
application_key    = your_application_key
application_secret = your_application_secret
endpoint           = ovh-eu
consumer_key       = your_consumer_key
```

-----

## Install using Composer

### First step

```
composer create-project jbelien/ovh-monitoring
```

### Second step

Create `monitoring.ini` file next to `public` directory (see [Configuration](#configuration)).

## Install using Docker

### First step

Build image from [GitHub](https://github.com/anmolnagpal/ovh-api-monitoring):
```
docker build --rm -t anmolnagpal/ovh-api-monitoring https://github.com/anmolnagpal/ovh-api-monitoring.git
```

**OR**

Pull image from [Docker Hub](https://hub.docker.com/r/anmolnagpal/ovh-api-monitoring/):
```
docker pull anmolnagpal/ovh-api-monitoring
```

### Second step

Create `monitoring.ini` file (see [Configuration](#configuration)).

### Third step

Run Docker container with your `monitoring.ini` mount as volume:

```
docker run --rm -p 80:80 -v "$PWD/monitoring.ini:/var/www/html/monitoring.ini" anmolnagpal/ovh-api-monitoring
```

**Warning:** You maybe will have to fix the path to `monitoring.ini` file (replace `$PWD/monitoring.ini` by the correct path).

### Fourth step

Go to http://myserver/ (using port `80`) where `myserver` is the IP address of your server to have a look a the monitoring tool.
