#
# Pick the Prestashop versions you want to test
#
#FROM docker.io/bitnami/prestashop:1.7
FROM --platform=linux/amd64 docker.io/bitnami/prestashop:8

# Add KohortPay module
USER root
COPY kohortpay /opt/bitnami/prestashop/modules/kohortpay

# Fix Mutex posixsem for Mac M1
RUN echo 'Mutex posixsem' >> /opt/bitnami/apache/conf/httpd.conf