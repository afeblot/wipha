/*
Copyright Alexandre Feblot, 2005-2006
http://globs.org

This software is a computer program whose purpose is to let people share
their iPhoto Library on the web, and let their users easily download a
bundle of pictures.

This software is governed by the CeCILL  license under French law and
abiding by the rules of distribution of free software.  You can  use, 
modify and/ or redistribute the software under the terms of the CeCILL
license as circulated by CEA, CNRS and INRIA at the following URL
"http://www.cecill.info". 

As a counterpart to the access to the source code and  rights to copy,
modify and redistribute granted by the license, users are provided only
with a limited warranty  and the software's author,  the holder of the
economic rights,  and the successive licensors  have only  limited
liability. 

In this respect, the user's attention is drawn to the risks associated
with loading,  using,  modifying and/or developing or reproducing the
software by the user in light of its specific status of free software,
that may mean  that it is complicated to manipulate,  and  that  also
therefore means  that it is reserved for developers  and  experienced
professionals having in-depth computer knowledge. Users are therefore
encouraged to load and test the software's suitability as regards their
requirements in conditions enabling the security of their systems and/or 
data to be ensured and,  more generally, to use and operate it in the 
same conditions as regards security. 

The fact that you are presently reading this means that you have had
knowledge of the CeCILL license and that you accept its terms.
*/

#include <sys/types.h>  // chmod
#include <sys/stat.h>   // chmod, stat
#include <stdio.h>
#include <string.h> // strn...
#include <stdlib.h> // exit
#include <unistd.h> // geteuid
#include <pwd.h>    // getpwuid

//char rootpath[255];
//char pathfile[512];

//=============================================================
void error(char* fnct, char* file) {
    char msg[101];
    strncpy(msg, fnct, 100);
    strncat(msg, " " , 100);
    strncat(msg, file, 100);
    perror(msg);
}

//=============================================================
int correctPerm(char * file) {
    static struct stat buf;
    static mode_t m;
    static int ret;
    
    //fprintf(stderr, "changeperm: %s\n", file); 

    // Get the current permission
    ret = stat(file, &buf);
    if (ret!=0) {
        error("stat", file);
        return ret;
    }
    m = buf.st_mode;
    
    // Add the required permission
    if (S_ISDIR(m)) {
        if ((m&S_IXOTH)==0) {
            ret = chmod(file, m|S_IXOTH); // dir : chmod o+x
            fprintf(stderr, "chmod o+x %s\n", file); 
        }
    } else if (S_ISREG(m)) {
        if ((m&S_IROTH)==0) {
            ret = chmod(file, m|S_IROTH); // file: chmod o+r
            fprintf(stderr, "chmod o+r %s\n", file); 
        }
    }
    if (ret!=0) {
        error("chmod", file);
    }
    return ret;
}

//=============================================================
void authControl() {
    uid_t myuid = geteuid();
    uid_t uid   = getuid();
    if ((uid!=myuid)&&(uid!=70)) {  // 70 = www
        struct passwd *pwd = getpwuid(uid);
        fprintf(stderr, "%s not allowed to run this program.\n", pwd->pw_name);
        exit(2);
    }
}

// //=============================================================
// void initRootPath() {
//     uid_t myuid = geteuid();
//     struct passwd *pwd = getpwuid(myuid);
//     snprintf(rootpath, 255, "/Users/%s/Sites/wipha/ipl/", pwd->pw_name);
// }

//=============================================================
int main(int argc, char ** argv) {
    int i;
    int ret = 0;
    char * file;
    
    authControl();
//     initRootPath();
    
    if (argc>1) {
        // Use arguments as parameters
        for (i=1; i<argc; i++) {
            file = argv[i];
            ret |= correctPerm(file);
        }
    } else {
        char file[255];
        char c;
        int pos = 0;
        // Use stdin as parameters
        // parameters are separated by spaces and may be included in " or '
        // if they contain spaces.
        do {
            c=getchar();
            if (c==10 || c==13 || c==EOF) {
                file[pos++] = 0;
                if (pos>1) {
                    ret |= correctPerm(file);
                }
                pos = 0;
                if (c==EOF) break;
            } else {
                if (pos<253) {
                    file[pos++] = c;
                } else {
                    file[pos++] = 0;
                    fprintf(stderr, "Error: Too long parameter: %s\n", file);
                    exit(-1);
                }
            }
        } while(c!=EOF);
    }
    return ret;
}
