FROM centos:6.8

MAINTAINER CDT <webdev@cpwonlinesolutions.com>

#####################################
# Non-Root User:
#####################################
ARG PUID=501
ARG PGID=501
ARG PUSER=application
ARG PGROUP=application
# Add a non-root user to prevent files being created with root permissions on host machine.
RUN groupadd -g $PGID $PGROUP && \
    useradd -u $PUID -g $PGROUP -m $PUSER

#####################################
# Set Timezone
#####################################
ARG TZ=Europe/London
ENV TZ=${TZ}
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN echo "export HTTP_PROXY=${HTTP_PROXY}" >> /etc/profile.d/proxy.sh \
    echo "export HTTPS_PROXY=${HTTPS_PROXY}" >> /etc/profile.d/proxy.sh \
    echo "export http_proxy=${HTTP_PROXY}" >> /etc/profile.d/proxy.sh \
    echo "export https_proxy=${HTTPS_PROXY}" >> /etc/profile.d/proxy.sh

######################################
## DEPENDENCIES
######################################
RUN bash -lc "yum -y install \
    git \
    mlocate \
    automake \
    libtool \
    flex \
    bison \
    pkgconfig \
    gcc-c++ \
    boost-devel \
    libevent-devel \
    zlib-devel \
    python-devel \
    ruby-devel \
    curl \
    vim-enhanced \
    fontconfig \
    acl"

#######################################
### APAHCE
#######################################
RUN bash -lc "yum -y install \
    httpd \
    mod_ssl \
    openssl"

ADD ./docker/httpd/https-keys /usr/local/bin
RUN chmod +x /usr/local/bin/https-keys
RUN https-keys && usermod -u 1000 apache

#######################################
### PHP
#######################################
RUN bash -lc "rpm -Uvh http://dl.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm \
    https://mirror.webtatic.com/yum/el6/latest.rpm" \
    && bash -lc "yum install -y \
    php55w \
    php55w-bcmath \
    php55w-cli \
    php55w-common \
    php55w-devel \
    php55w-enchant \
    php55w-gd \
    php55w-imap \
    php55w-intl \
    php55w-ldap \
    php55w-mbstring \
    php55w-mcrypt \
    php55w-mysqlnd \
    php55w-pdo \
    php55w-pspell \
    php55w-soap \
    php55w-xml" \
    && sed -i "s,;date.timezone =,date.timezone=$TZ,g" /etc/php.ini

######################################
## xDebug:
######################################
ARG INSTALL_XDEBUG=false
RUN if [ ${INSTALL_XDEBUG} = true ]; then \
    # Install the xdebug extension
    bash -lc "yum install -y php55w-pecl-xdebug" \
;fi

# Copy xdebug configration for remote debugging
COPY ./docker/php/templates/xdebug.ini /etc/php/conf.d/xdebug.ini

######################################
## MongoDB:
######################################
ARG INSTALL_MONGO=false
RUN if [ ${INSTALL_MONGO} = true ]; then \
    # DOESN'T WORK .
    # Install the mongodb extension
    bash -lc "yum install -y openssl-devel" && \
    bash -lc "pecl install mongodb" && \
    echo "extension=mongodb.so" >> /etc/php/conf.d/mongodb.ini \
;fi

######################################
## ZipArchive:
######################################
ARG INSTALL_ZIP_ARCHIVE=false
RUN if [ ${INSTALL_ZIP_ARCHIVE} = true ]; then \
    # Install the zip extension
    bash -lc "yum install -y php55w-pecl-zip" && \
    bash -lc "yum clean all" \
;fi

######################################
## PHP Memcached:
######################################
ARG INSTALL_MEMCACHED=false
RUN if [ ${INSTALL_MEMCACHED} = true ]; then \
#    DOESN'T WORK.
    # Install the php memcached extension
    bash -lc "pecl install memcached" && \
#    echo "extension=memcache.so" >> /etc/php.d/memcache.ini
    bash -lc "yum clean all" \
;fi

######################################
## Opcache:
######################################
ARG INSTALL_OPCACHE=false
RUN if [ ${INSTALL_OPCACHE} = true ]; then \
    bash -lc "yum install -y php55w-opcache" && \
    bash -lc "yum clean all" \
;fi

# Copy opcache configration
COPY ./docker/php/templates/opcache.ini /etc/php/conf.d/opcache.ini

######################################
## Composer:
######################################
ARG INSTALL_COMPOSER=false
ENV INSTALL_COMPOSER=${INSTALL_COMPOSER}
# Check if global install need to be ran
RUN if [ ${INSTALL_COMPOSER} = true ]; then \
    # install
    bash -lc "curl -sS https://getcomposer.org/installer | php" && \
    mv composer.phar /usr/local/bin/composer \
;fi


USER $PUSER
######################################
## ssh:
######################################
#ARG INSTALL_WORKSPACE_SSH=false
#ENV INSTALL_WORKSPACE_SSH=${INSTALL_WORKSPACE_SSH}
#COPY ./docker/ssh/templates/id_rsa /tmp/id_rsa
#COPY ./docker/ssh/templates/id_rsa.pub /tmp/id_rsa.pub
#COPY ./docker/ssh/templates/stash-known-hosts-key /tmp/known_hosts
#
#RUN if [ ${INSTALL_WORKSPACE_SSH} = true ]; then \
##        yum install -y openssh openssh-server \
##    rm -f /etc/service/sshd/down && \
#        mkdir -p /home/${PUSER}/.ssh \
#        && chmod -R 0700 /home/${PUSER}/.ssh \
#        && cat /tmp/id_rsa.pub >> /home/${PUSER}/.ssh/id_rsa.pub \
#        && cat /tmp/id_rsa >> /home/${PUSER}/.ssh/id_rsa \
#        && cat /tmp/known_hosts >> /home/${PUSER}/.ssh/known_hosts \
#        && chmod 644 /home/${PUSER}/.ssh/id_rsa.pub /home/${PUSER}/.ssh/known_hosts \
#        && chmod 400 /home/${PUSER}/.ssh/id_rsa \
#;fi

USER $PUSER

######################################
## Node / NVM:
######################################
ARG INSTALL_NODE=false
ENV INSTALL_NODE=${INSTALL_NODE}
ARG NODE_VERSION=stable
ENV NODE_VERSION=${NODE_VERSION}
ENV NVM_DIR=/home/${PUSER}/.nvm
## Check if NVM needs to be installed
RUN if [ ${INSTALL_NODE} = true ]; then \
    # Install nvm (A Node Version Manager)
    bash -lc "curl https://raw.githubusercontent.com/creationix/nvm/v0.31.6/install.sh | bash" && \
        . $NVM_DIR/nvm.sh && \
        bash -lc "nvm install ${NODE_VERSION}" && \
        nvm use ${NODE_VERSION} && \
        nvm alias ${NODE_VERSION} && \
        bash -lc "npm install -g gulp bower" \
;fi

## Wouldn't execute when added to the RUN statement in the above block
## Source NVM when loading bash since ~/.profile isn't loaded on non-login shell
RUN if [ ${INSTALL_NODE} = true ]; then \
    echo "" >> ~/.bashrc && \
    echo 'export NVM_DIR="$HOME/.nvm"' >> ~/.bashrc && \
    echo '[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh"  # This loads nvm' >> ~/.bashrc \
;fi

######################################
## YARN:
######################################

USER $PUSER

ARG INSTALL_YARN=false
ENV INSTALL_YARN=${INSTALL_YARN}
RUN if [ ${INSTALL_YARN} = true ]; then \
    [ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh" && \
    bash -lc "curl -o- -L https://yarnpkg.com/install.sh | bash" && \
    echo "" >> ~/.bashrc && \
    echo 'export PATH="$HOME/.yarn/bin:$PATH"' >> ~/.bashrc \
;fi

## Add YARN binaries to root's .bashrc
USER root

RUN if [ ${INSTALL_YARN} = true ]; then \
    echo "" >> ~/.bashrc && \
    echo 'export YARN_DIR="/home/$PUSER/.yarn"' >> ~/.bashrc && \
    echo 'export PATH="$YARN_DIR/bin:$PATH"' >> ~/.bashrc \
;fi

RUN rm -rf /tmp/* /var/tmp/*

##
##--------------------------------------------------------------------------
## Final Touch
##--------------------------------------------------------------------------
##
ARG WEB_DIR=/var/www/live
ENV WEB_DIR=${WEB_DIR}

#COPY ./docker/entrypoint.sh /usr/local/bin
#RUN chmod +x /usr/local/bin/entrypoint.sh

USER root

COPY ./docker/httpd/templates/vhost.conf /etc/httpd/conf.d/

EXPOSE 80 443 9000

WORKDIR $WEB_DIR

USER root

CMD [ "httpd", "-D", "FOREGROUND" ]