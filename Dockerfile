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
RUN sed -i "s/8080/\${PORT}/g" /opt/bitnami/apache/conf/httpd.conf
RUN sed -i "s/APACHE_HTTP_PORT_NUMBER/PORT/g" /opt/bitnami/scripts/apache/setup.sh /opt/bitnami/scripts/apache-env.sh /opt/bitnami/scripts/libapache.sh