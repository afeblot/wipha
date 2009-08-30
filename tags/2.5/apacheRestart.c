#include <unistd.h>

int main() {
    setuid(0);
    execl("/usr/sbin/apachectl", "apachectl", "restart" , (char *)0);
    return 0;
}
