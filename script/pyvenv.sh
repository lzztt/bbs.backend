cd script/
python3 -m venv .venv
source .venv/bin/activate
pip install pylint
pip install mysql-connector-python
pip install inflection
# python sync_dbobject.py hbbs ../server/dbobject/
