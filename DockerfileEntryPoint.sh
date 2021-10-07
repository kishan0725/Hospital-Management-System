#!/bin/bash
set -e

mkdir -p /var/log/apache2/
chmod -R 750 /var/log/apache2/

ENV_FILE=env
ENV_HOME=/var

function echo_log {
  DATE='date +%Y/%m/%d:%H:%M:%S'
  echo `$DATE`" $1"
}

function download_env_variables {

  if [[ "x${CONFIGURATOR_GET_VARIABLES_FULL_URL}" = "x"  || "x${CONFIGURATOR_AUTH_HEADER}" = "x" ]]; then
    echo_log ""
    echo_log "Configurator variables are not provided. Variables will not be downloaded"
    return 0
  fi

 echo_log ""
 echo_log "starting to download environment variables"
 http_response=$(curl -s -o curl_response_file -w "%{http_code}" -H "$CONFIGURATOR_AUTH_HEADER" ${CONFIGURATOR_GET_VARIABLES_FULL_URL})

 echo_log "download status:  $http_response"
 if [ $http_response == "200" ]; then
    mv curl_response_file $ENV_HOME/$ENV_FILE
    echo_log "exporting Environment Variables"
    source $ENV_HOME/$ENV_FILE
 else

    echo_log "download response: $(cat curl_response_file)"
    echo_log "new environment variables could not be obtained from remote service"

    if [ -e $ENV_HOME/$ENV_FILE ]; then
       echo_log "exporting old env variables"
       source $ENV_HOME/$ENV_FILE
    else
       echo_log "environment file not found: $ENV_HOME/$ENV_FILE"
       echo_log "if variables will not download, don't pass CONFIGURATOR_ variables and try again."
       exit 1
    fi
 fi

}

function start {
  php -v
  source /etc/apache2/envvars
  exec apache2 -DFOREGROUND
}


########################
# Scripts starts here
########################
download_env_variables
start
