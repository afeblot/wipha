VER=$(shell awk -F\" '/version/ {print $$2}' wipha/configs/wipha.conf)
DMG=WiPhA_v$(VER).dmg
DMG_ROOT=/tmp/wiphaDmgRoot
DMG_IMG_DIR=$(DMG_ROOT)/doc/img
DMG_SRC_DIR=$(DMG_ROOT)/src
DMG_LICENSE=$(DMG_ROOT)/LICENSE.txt
DMG_README=$(DMG_ROOT)/README.html
INSTALLER=$(DMG_ROOT)/WiPhA\ Installer.app
TBZ=wipha.tbz
INSTALL_SCRIPT=install_script
CHANGEPERM=wipha/changeperm
APACHE_RESTART=apacheRestart
GET_TRUE_NAME=wipha/getTrueName

.PHONY: default dmgBaseContent help src clean

default: $(DMG)

$(DMG): dmgBaseContent $(INSTALLER) help src
	hdiutil create -ov -fs HFS+ -scrub -srcfolder $(DMG_ROOT) -volname "WiPhA $(VER)" "$@"

dmgBaseContent: $(DMG_IMG_DIR) $(DMG_SRC_DIR) $(DMG_LICENSE) $(DMG_README)
$(DMG_IMG_DIR):
	@ mkdir -p $(DMG_ROOT)/doc/img
$(DMG_SRC_DIR):
	@ mkdir -p $(DMG_ROOT)/src
$(DMG_LICENSE): build_resources/LICENSE.txt
	@ cp "$<" "$@"
$(DMG_README):
	@ [ -h "$@" ] || ln -s doc/index.html "$@"
    

$(INSTALLER): $(INSTALL_SCRIPT) build_installer $(TBZ) $(APACHE_RESTART)
	./build_installer $(INSTALL_SCRIPT) "$@" $(TBZ) $(APACHE_RESTART) "$(VER)"

$(TBZ): $(CHANGEPERM) $(GET_TRUE_NAME) FORCE
	@ cp build_resources/LICENSE.txt wipha/
	tar jcf "$@" \
        --exclude ".svn" \
        --exclude "wipha/test.*" \
        --exclude "wipha/data/*.ser" \
        --exclude "wipha/data/*.dat" \
        --exclude "wipha/data/cache/*.*" \
        --exclude "wipha/3rdParty/phpZipLight" \
        wipha

$(VER): wipha/configs/wipha.conf
	@ awk -F\" '/version/ {print $$2}' "$<" > "$@"

# Carbon framework only required for getTrueName
wipha/% : %.c
	@ echo "Build $@" ; \
	gcc -Wall -arch i386 -arch x86_64 -isysroot /Developer/SDKs/MacOSX10.6.sdk -mmacosx-version-min=10.6 -o "$@" "$<" -framework Carbon ; chmod 4755 "$@"

% : %.c
	@ echo "Build $@" ; \
	gcc -Wall -arch i386 -arch x86_64 -isysroot /Developer/SDKs/MacOSX10.6.sdk -mmacosx-version-min=10.6 -o "$@" "$<"

HELP_FILES=install.html \
           admin.html \
           intro.html \
           index.html \
           usage.html \
           history.html

TRGD=$(DMG_ROOT)/doc
SRCD=$(DMG_ROOT)/src
TPLD=wipha/templates
RSCD=build_resources/doc
IDX=TPLD/help_index.tpl
DMG_HELP=$(HELP_FILES:%=$(DMG_ROOT)/doc/%)
GUPPY_SRC=$(filter-out install.html,$(HELP_FILES))
GUPPY_HELP=$(GUPPY_SRC:%=guppydoc/%)

help: $(DMG_HELP) $(GUPPY_HELP) FORCE
	@ cp $(RSCD)/*.jpg $(TRGD)/img
	@ cp $(RSCD)/wiphadoc.css $(TRGD)/img
	@ cp wipha/img/* $(TRGD)/img
	@ cp wipha/skin/orig/wiphacommon.css $(TRGD)/img
	@ cd guppydoc; if [ $$(cat intro.html|wc -l) == "1" ]; then \
                        cat intro.html >> index.html; mv index.html intro.html; \
                    fi

src: apacheRestart.c changeperm.c getTrueName.c
	@ cp $? $(SRCD)/
# DMG doc from wipha templates
$(TRGD)/%.html: $(TPLD)/help_%.tpl   $(RSCD)/header.html $(RSCD)/menu.html $(RSCD)/footer.html
	@ echo "Build $@" ; \
    echo "" > "$@"; \
	cat $(RSCD)/header.html >> "$@"; \
    cat "$<" >> "$@"; \
    if [ "$<" == "$(TPLD)/help_index.tpl" ]; then \
	    cat $(RSCD)/menu.html >> "$@"; \
    fi; \
    cat $(RSCD)/footer.html >> "$@"

# DMG additional doc (install, history)
$(TRGD)/%.html: $(RSCD)/%.html   $(RSCD)/header.html $(RSCD)/footer.html
	@ echo "Build $@" ; \
	echo "" > "$@"; \
	cat $(RSCD)/header.html >> "$@"; \
    cat "$<" >> "$@"; \
    cat $(RSCD)/footer.html >> "$@"

# GuppY doc from wipha templates
guppydoc/%.html: $(TPLD)/help_%.tpl
	@ echo "Build $@" ; \
	tr -s "\n\t" "  " < "$<" | sed -e "s/img\//img\/wipha\//g" > "$@"

# GuppY additional doc
guppydoc/%.html: $(RSCD)/%.html
	@ echo "Build $@" ; \
	tr -s "\n\t" "  " < "$<" | sed -e "s/img\//img\/wipha\//g" > "$@"


FORCE:

clean:
	@ rm -rf $(TBZ) $(DMG) $(DMG_ROOT) $(GUPPY_HELP) $(APACHE_RESTART) $(CHANGEPERM) $(GET_TRUE_NAME)

essai.app: 
	./build_installer essai_script "$@" pipo "$(VER)"
	@ cp wipha.pax essai.app/Contents/Resources

test:
	@echo $(GUPPY_HELP)
