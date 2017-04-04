import os
import configparser
from datetime import date

step = 0

def printStep(message):
    '''
    Prints Steps
    '''
    global step
    step=step+1
    print('Step' + str(step) + ': ' + message)

def main():
    settings = configparser.ConfigParser()
    settings.read('./settings.ini')

    print('MyBB Plugin Creator ' + settings['DEFAULT']['version'])

    mybbPath = settings['PATHS']['MyBBPath']
    resPath = settings['PATHS']['ResourcesPath']

    dummyPluginFile = resPath + '\\dummyPlugin.php'

    if not os.path.isfile(dummyPluginFile):
        print('dummyPlugin.php not found in ' + dummyPluginFile)

    pluginFolderPath = os.path.join(mybbPath, 'inc\\plugins')
    languagesFolderPath = os.path.join(mybbPath, 'inc\\languages\\english')
    adminLanguagesFolderPath = os.path.join(mybbPath, 'inc\\languages\\english\\admin')
    adminFolderPath = os.path.join(mybbPath, 'admin')
    adminModulesFolderPath = os.path.join(adminFolderPath, 'modules')

    if not os.path.exists(pluginFolderPath):
        print('Warning: ' + pluginFolderPath + ' path does not exist.')
    if not os.path.exists(languagesFolderPath):
        print('Warning: ' + languagesFolderPath + ' path does not exist.')
    if not os.path.exists(adminLanguagesFolderPath):
        print('Warning: ' + adminLanguagesFolderPath + ' path does not exist.')
    if not os.path.exists(adminFolderPath):
        print('Warning: ' + adminFolderPath + ' path does not exist.')
    if not os.path.exists(adminModulesFolderPath):
        print('Warning: ' + adminModulesFolderPath + ' path does not exist.')

    pluginName = str(input("Plugin Name: "))
    pluginFriendlyName = str(input("Plugin Friendly Name: "))
    pluginDescription = str(input("Plugin Description: "))

    languageFileName = pluginName + '.lang.php'
    pluginFileName = pluginName + '.php'

    # Generate language strings:
    adminLangStrings = "\n$l['{pluginname}_uninstall'] = 'Uninstalling {pluginname}';\n"
    adminLangStrings = adminLangStrings + "$l['{pluginname}_uninstall_message'] = 'Are you sure you want to uninstall {pluginname}?';\n"

    adminLangString = adminLangStrings.replace('{pluginname}', pluginName)

    # Create plugin language files
    with open(os.path.join(languagesFolderPath, languageFileName), 'w') as file:
        file.write('<?php\n\n?>')

    with open(os.path.join(adminLanguagesFolderPath, languageFileName), 'w') as file:
        file.write('<?php\n' + adminLangString + '\n?>')

    printStep('Created language files.')

    # Create the main plugin file
    with open(dummyPluginFile, 'rt') as dummyPlugin:
        dummyPluginData = dummyPlugin.read()

    dummyPluginData = dummyPluginData.replace('{pluginname}', pluginName)
    dummyPluginData = dummyPluginData.replace('{pluginnameUppercase}', pluginName.upper())
    dummyPluginData = dummyPluginData.replace('{pluginfriendlyname}', pluginFriendlyName)
    dummyPluginData = dummyPluginData.replace('{plugindescription}', pluginDescription)
    dummyPluginData = dummyPluginData.replace('{copyyear}', str(date.today().year))
    dummyPluginData = dummyPluginData.replace('{pluginwebsite}', settings['PLUGINSETTINGS']['PluginWebsite'])
    dummyPluginData = dummyPluginData.replace('{author}', settings['PLUGINSETTINGS']['Author'])
    dummyPluginData = dummyPluginData.replace('{authorsite}', settings['PLUGINSETTINGS']['AuthorSite'])
    dummyPluginData = dummyPluginData.replace('{defaultversion}', settings['PLUGINSETTINGS']['DefaultVersion'])
    dummyPluginData = dummyPluginData.replace('{compatibility}', settings['PLUGINSETTINGS']['Compatibility'])
    dummyPluginData = dummyPluginData.replace('{copyright}', settings['PLUGINSETTINGS']['Copyright'])
    dummyPluginData = dummyPluginData.replace('{extraplugininfo}', settings['PLUGINSETTINGS']['ExtraPluginInfo'])

    with open(os.path.join(pluginFolderPath, pluginFileName), 'w') as file:
        file.write(dummyPluginData)

    printStep('Plugin File Created.')

    requireAdminModules = str(input("Do you require a separate admin module for your plugin: "))

    if requireAdminModules.lower() == 'yes':
        # Create module folder
        if not os.path.exists(os.path.join(adminModulesFolderPath, pluginName)):
            os.mkdir(os.path.join(adminModulesFolderPath, pluginName))
        with open(os.path.join(adminModulesFolderPath, pluginName + '\\' + pluginFileName), 'w') as file:
            file.write('<?php\n\n?>')

        with open(os.path.join(adminModulesFolderPath, pluginName + '\\module_meta.php'), 'w') as file:
            file.write('<?php\n\n?>')

        printStep('Admin Module created.')

    print('Finished: Plugin folders and files created successfully.')

if __name__ == '__main__':
    main()
