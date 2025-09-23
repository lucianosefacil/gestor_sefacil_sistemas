FROM ubuntu:20.04

# Atualiza pacotes básicos e instala ferramentas necessárias
RUN apt-get update && apt-get install -y \
    build-essential manpages-dev wget curl gawk bison

# Baixa e instala a versão correta da GLIBC
WORKDIR /usr/src
RUN wget http://ftp.gnu.org/gnu/libc/glibc-2.34.tar.gz && \
    tar -xvzf glibc-2.34.tar.gz && \
    cd glibc-2.34 && \
    mkdir build && cd build && \
    ../configure --prefix=/opt/glibc-2.34 && \
    make -j$(nproc) && \
    make install

# Define o novo caminho da GLIBC no sistema
ENV LD_LIBRARY_PATH=/opt/glibc-2.34/lib:$LD_LIBRARY_PATH
ENV PATH=/opt/glibc-2.34/bin:$PATH

# Baixa o binário do go-crypto-plugin
RUN wget "https://storage.googleapis.com/go-crypto-plugin/stable/linux/go-crypto-plugin.bin" -O /var/www/go-crypto-plugin.bin

# Dá permissão de execução ao binário
RUN chmod +x /var/www/go-crypto-plugin.bin

# Cria diretório temporário necessário
RUN mkdir -p /var/www/temp

WORKDIR /var/www

# Define o ponto de entrada do container
ENTRYPOINT ["/var/www/go-crypto-plugin.bin"]

