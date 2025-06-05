# URL Shortener

## Introduction

This is a URL shortener application built using Laravel and Vue.JS. It's a project focused on learning about software architecture concepts, such as: 

- Race condition
- Data partitioning and scalability
- Caching strategies
- Idempotency API design
- And more...

### Requirements

- Docker
- Docker Compose

As an intentional choice, the nginx server is configured to simulate a production environment. This means that you will need to add the following lines to your `/etc/hosts` file:

```bash
echo -e "127.0.0.1 api.localhost.com" | sudo tee -a /etc/hosts
```

## Installation

1. Clone the repository:

```bash
git clone https://github.com/gustavocabreira/url-shortener.git
```

2. Navigate to the project directory:

```bash
cd url-shortener
```

3. Install the project dependencies:

```bash
cd docker/local
sh install.sh --app-name=url-shortener
```

4. Access the application at `http://api.localhost.com`.