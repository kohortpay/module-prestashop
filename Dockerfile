#
# Pick the Prestashop versions you want to test
#

# Prestashop 1.6
#FROM docker.io/bitnami/prestashop:1.6.1.9-r5

# Prestashop 1.7    
FROM docker.io/bitnami/prestashop:1.7.6-8

# Prestashop 8
#FROM --platform=linux/amd64 docker.io/bitnami/prestashop:8

# Add KohortPay module
USER root
COPY kohortpay /opt/bitnami/prestashop/modules/kohortpay

# Fix Mutex posixsem for Mac M1
RUN echo 'Mutex posixsem' >> /opt/bitnami/apache/conf/httpd.conf