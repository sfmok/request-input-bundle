{
  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";
    phps.url = "github:fossar/nix-phps";
    devenv.url = "github:cachix/devenv";
    systems.url = "github:nix-systems/default";
  };

  outputs = inputs @ { self, flake-parts, ... }: flake-parts.lib.mkFlake { inherit inputs; } {
    systems = import inputs.systems;

    imports = [
      inputs.devenv.flakeModule
    ];

    perSystem = { config, self', inputs', pkgs, system, lib, ... }: {
      _module.args.pkgs = import self.inputs.nixpkgs {
        inherit system;
        overlays = [
          inputs.phps.overlays.default
        ];
      };

      devenv.shells.default = {
        name = "php-dev-env";

        packages = [
          pkgs.php82
          pkgs.php82.packages.composer
        ];
      };
    };
  };
}
