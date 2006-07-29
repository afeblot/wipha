VER=$(shell awk -F\" '/version/ {print $$2}' wipha/configs/wipha.conf)
DMG=WiPhA_v$(VER).dmg
INSTALLER=dmgContent/WiPha\ Installer.app
TBZ=wipha.tbz
INSTALL_SCRIPT=install_script
PERM=wipha/changeperm

default: $(DMG)
all: $(PERM) help $(DMG)

$(DMG): $(INSTALLER) help
	@ cp build_resources/LICENSE.txt dmgContent/
	hdiutil create -ov -fs HFS+ -srcfolder dmgContent -volname "WiPhA $(VER)" "$@"

$(INSTALLER): $(INSTALL_SCRIPT) build_installer $(TBZ)
	./build_installer $(INSTALL_SCRIPT) "$@" $(TBZ) "$(VER)"
	@ cp $(TBZ) $(INSTALLER)/Contents/Resources # Corrects a Platypus bug: the file is not bundled

$(TBZ): $(PERM) FORCE
	@ cp build_resources/LICENSE.txt wipha/
	tar jcf "$@" wipha \
        --exclude "wipha/test.*" \
        --exclude "wipha/data/*.ser" \
        --exclude "wipha/data/*.dat" \
        --exclude "wipha/data/cache/*" \
        --exclude "wipha/3rdParty/phpZipLight"

$(VER): wipha/configs/wipha.conf
	@ awk -F\" '/version/ {print $$2}' "$<" > "$@"

wipha/% : %.c
	@ echo "Build $@" ; \
	gcc -Wall -o "$@" "$<" ; chmod 4755 "$@"

HELP_FILES=install.html \
           admin.html \
           intro.html \
           index.html \
           usage.html \
           history.html

TRGD=dmgContent/doc
TPLD=wipha/templates
RSCD=build_resources/doc
IDX=TPLD/help_index.tpl
DMG_HELP=$(HELP_FILES:%=dmgContent/doc/%)
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
	@ rm -rf $(TBZ) $(DMG) $(INSTALLER) $(DMG_HELP) $(GUPPY_HELP) dmgContent/doc/img/* dmgContent/LICENSE.txt

essai.app: 
	./build_installer essai_script "$@" pipo "$(VER)"
	@ cp wipha.pax essai.app/Contents/Resources

test:
	@echo $(GUPPY_HELP)
