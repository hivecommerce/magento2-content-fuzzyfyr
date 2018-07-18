#!/usr/bin/env bash
##
## Add entry to /etc/hosts: 10.254.254.254 magento2.test
##
echo "Init..."
WORKDIR=$(pwd)
INSTALLDIR="${WORKDIR}/src"
M2SETUP_VERSION=2.2.4

echo "Work Dir: ${WORKDIR}"
echo "Install Dir: ${INSTALLDIR}"
echo "Magento Version: ${M2SETUP_VERSION}"

if [ ! -d "${INSTALLDIR}" ]; then
    mkdir -p "${INSTALLDIR}"
fi

#
# Prepare Magento 2
#
echo "Start installment..."
${WORKDIR}/bin/download ${M2SETUP_VERSION}
${WORKDIR}/bin/start

sleep 10

${WORKDIR}/bin/setup
