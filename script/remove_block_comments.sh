grep -E '^(/\*| \*)' `find . -type f`
sed -Ei 's!^(/\*| \*).*!!' `find . -type f`
perl -0777 -i -pe 's/\n\n\n/\n\n/igs' `find . -type f`
perl -0777 -i -pe 's/\n\n\n/\n\n/igs' `find . -type f`
perl -0777 -i -pe 's/\n\n\n/\n\n/igs' `find . -type f`
