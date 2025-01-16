# Base image with PHP 7.4
FROM php:7.4-cli

# Arguments to capture UID and GID
ARG HOSTUID
ARG HOSTGID

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install necessary tools
RUN apt-get update && apt-get install -y \
    sudo \
    git \
    unzip \
    && apt-get clean

# Create a group and user with matching UID/GID
RUN groupadd -g ${HOSTGID} developer \
    && useradd -m -u ${HOSTUID} -g ${HOSTGID} -s /bin/bash developer \
    && usermod -aG sudo developer \
    && echo "developer ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Set permissions for the working directory
WORKDIR /app
RUN chown -R developer:developer /app

# Switch to the new user
USER developer

# Default shell command
CMD ["/bin/bash"]
