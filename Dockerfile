FROM ubuntu

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update
RUN apt-get install -y software-properties-common
RUN add-apt-repository -y ppa:ondrej/php
RUN apt-get update -y

RUN apt-get install -y apache2
RUN apt-get install -y php7.2 libapache2-mod-php7.2 php7.2-cli php7.2-common php7.2-mbstring php7.2-gd php7.2-intl php7.2-xml php7.2-mysql php7.2-mcrypt php7.2-zip

RUN mkdir -p /var/www/html
COPY . /var/www/html

WORKDIR /var/www/html

## configure apache

# remove ubuntu apache default page
RUN rm /var/www/html/index.html

ENV CUSTOM_DOCUMENT_ROOT=\/var\/www\/html

# fix warning
# AH00558: apache2: Could not reliably determine the server's fully qualified domain name,
# using 172.17.0.2. Set the 'ServerName' directive globally to suppress this message
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN echo "<Directory $CUSTOM_DOCUMENT_ROOT>" >> /etc/apache2/apache2.conf
RUN echo "    Options Indexes FollowSymLinks" >> /etc/apache2/apache2.conf
RUN echo "    AllowOverride All" >> /etc/apache2/apache2.conf
RUN echo "    Require all granted" >> /etc/apache2/apache2.conf
RUN echo "</Directory>" >> /etc/apache2/apache2.conf
RUN a2enmod rewrite

RUN mkdir -p /var/log/apache2/
RUN chmod -R 750 /var/log/apache2/
RUN chown -R www-data:www-data /var/log/apache2/
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 755 /var/www/html/

# final docker configuration

COPY DockerfileEntryPoint.sh /usr/local/bin/DockerfileEntryPoint.sh
RUN chmod 744 /usr/local/bin/DockerfileEntryPoint.sh

ENTRYPOINT ["DockerfileEntryPoint.sh"]
